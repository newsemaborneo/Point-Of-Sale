<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion; // Tambahkan baris ini
use App\Models\Voucher; // Tambahkan baris ini jika belum ada
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\Setting;

use App\Models\StockMovement;
use App\Models\Branch; // Tambahkan ini untuk mengakses relasi cabang dari pengguna
use App\Models\Warehouse; // Tambahkan ini untuk mengakses model Warehouse
use Illuminate\Support\Facades\Log; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Sale::with('customer')
            ->when($request->search, fn ($query, $search) => $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->when(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('supervisor'), function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->latest()->paginate(20);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $cashRegister = CashRegister::where('user_id', Auth::id())->where('status', 'open')->first();
        $products     = Product::where('is_active', true)->orderBy('name')->get();
        $customers    = Customer::orderBy('name')->get();
        $categories   = \App\Models\Category::orderBy('name')->get();

        // Tentukan ID gudang untuk kasir saat ini.
        // Prioritaskan warehouse_id dari cash register yang sedang dibuka.
        // Jika tidak ada atau cash register tidak memiliki warehouse_id,
        // coba dapatkan gudang pertama yang terkait dengan cabang pengguna.
        // Fallback ke ID gudang default (misalnya 1) jika tidak ada gudang spesifik yang dapat ditentukan.
        $warehouseId = null;
        if ($cashRegister && $cashRegister->warehouse_id) {
            $warehouseId = $cashRegister->warehouse_id;
        } elseif (Auth::user()->branch_id) {
            $userBranch = Auth::user()->branch; // Asumsi relasi 'branch' ada di model User
            if ($userBranch && $userBranch->warehouses->isNotEmpty()) {
                $warehouseId = $userBranch->warehouses->first()->id;
            }
        }
        $warehouseId = $warehouseId ?? 1; // Default ke gudang ID 1 jika tidak ditemukan

        // Fetch active promotions and vouchers
        $activePromotions = Promotion::where('is_active', true)->with('products')->get();
        $activeVouchers = Voucher::where('end_date', '>=', now())->orWhereNull('end_date')->get();

        $productsJson = $products->map(fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'price'       => (float) $p->sale_price,
            'stock'       => (int) $p->stockInWarehouse($warehouseId), // Gunakan stok dari gudang cabang
            'photo'       => $p->photo ?? null,
            'category_id' => $p->category_id,
        ])->values()->toArray();

        // Store hours from settings
        $storeHours = [
            'enabled' => (bool) Setting::get('store_hours_enabled', false),
            'open'    => Setting::get('store_open_time',  '08:00'),
            'close'   => Setting::get('store_close_time', '21:00'),
        ];

        return view('transactions.pos', compact(
            'productsJson', 'customers', 'cashRegister', 'categories', 'storeHours',
            'activePromotions', 'activeVouchers'
        ));
    }

    // Handles the actual transaction storage
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'discount_total' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'voucher_code' => 'nullable|string', // Added for voucher integration
        ]);

        $cashRegister = CashRegister::where('user_id', Auth::id())->where('status', 'open')->first();

        // Logika untuk mencegah input jika shift kasir belum dibuka
        if (!$cashRegister) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat memproses transaksi. Shift kasir belum dibuka.'
            ], 403); // Mengembalikan status 403 Forbidden
        }

        // Logika untuk mencegah input jika toko sedang tutup
        $storeHoursEnabled = (bool) Setting::get('store_hours_enabled', false);

        // Tentukan zona waktu lokal toko.
        // Untuk saat ini, kita akan mengasumsikan 'Asia/Jakarta' sebagai zona waktu lokal umum untuk pengguna di Indonesia.
        // Dalam sistem yang lebih kuat, ini harus dapat dikonfigurasi per cabang atau sebagai pengaturan global.
        $storeLocalTimezone = 'Asia/Jakarta'; // Placeholder, idealnya dari pengaturan atau konfigurasi cabang

        if ($storeHoursEnabled) {
            $storeOpenTime  = Setting::get('store_open_time',  '08:00');
            $storeCloseTime = Setting::get('store_close_time', '21:00');

            $now = now(); // Instance Carbon saat ini dalam zona waktu aplikasi
            // Buat instance Carbon untuk jam buka dan tutup hari ini dalam zona waktu lokal toko
            // Penting: setDate($now->year, $now->month, $now->day) memastikan kita membandingkan
            // waktu buka/tutup untuk HARI INI di zona waktu lokal toko.
            $openTimeLocal  = \Carbon\Carbon::createFromTimeString($storeOpenTime, $storeLocalTimezone)
                                            ->setDate($now->year, $now->month, $now->day);
            $closeTimeLocal = \Carbon\Carbon::createFromTimeString($storeCloseTime, $storeLocalTimezone)
                                            ->setDate($now->year, $now->month, $now->day);

            // Konversi waktu lokal ini ke zona waktu aplikasi untuk perbandingan yang akurat dengan $now
            // Ini adalah langkah krusial untuk menyamakan basis perbandingan.
            $openTimeAppTimezone  = $openTimeLocal->copy()->timezone($now->timezone);
            $closeTimeAppTimezone = $closeTimeLocal->copy()->timezone($now->timezone);

            $storeIsOpen = false;

            if ($closeTimeAppTimezone->lessThan($openTimeAppTimezone)) { // Overnight shift
                // If current time is after opening time OR before closing time (next day)
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeAppTimezone) || $now->lessThan($closeTimeAppTimezone);
            } else {
                // Normal closing (e.g., 08:00 - 21:00)
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeAppTimezone) && $now->lessThan($closeTimeAppTimezone);
            }

            if (!$storeIsOpen) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat memproses transaksi. Toko sedang tutup. Jam operasional: ' . $storeOpenTime . ' - ' . $storeCloseTime . '.'], 403);
            }
        }

        $warehouseId = $cashRegister->warehouse_id ?? 1; // Fallback ke ID gudang 1 jika null
        // Log the branch_id being used for the sale
        Log::info('Creating Sale with Branch ID: ' . $cashRegister->branch_id . ' and Warehouse ID: ' . $warehouseId);


        try {
            DB::beginTransaction();

            $sale = Sale::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'cash_register_id' => $cashRegister ? $cashRegister->id : null,
                'subtotal' => $request->subtotal, // This line was already there, no change needed.
                'warehouse_id' => $warehouseId, // Tambahkan warehouse_id di sini
                'branch_id' => $cashRegister->branch_id ?? Auth::user()->branch_id, // Gunakan branch_id dari cash register, fallback ke branch_id pengguna
                'discount_total' => $request->discount_total,
                'tax_total' => 0,
                'grand_total' => $request->grand_total,
                'paid_amount' => $request->paid_amount,
                'change_amount' => max(0, $request->paid_amount - $request->grand_total),
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'discount'   => 0,
                    'tax'        => 0,
                    'subtotal'   => $subtotal,
                ]);

                // Kurangi stok di gudang kasir
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $warehouseId],
                    ['quantity' => 0]
                );

                $quantityBefore = $stock->quantity;
                $stock->decrement('quantity', $item['quantity']);

                // Catat pergerakan stok
                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'warehouse_id'    => $warehouseId,
                    'type'            => 'sale',
                    'quantity'        => -$item['quantity'],
                    'quantity_before' => $quantityBefore,
                    'quantity_after'  => $quantityBefore - $item['quantity'],
                    'reference_type'  => Sale::class,
                    'reference_id'    => $sale->id,
                    'note'            => 'Penjualan invoice ' . $sale->invoice_number,
                    'user_id'         => Auth::id(),
                ]);
            }

            // Simpan data pembayaran
            // Jika relasi payments() belum ada di model Sale, kita bisa skip atau insert manual. Tapi menurut schema sebelumnya ada.
            if (method_exists($sale, 'payments')) {
                $sale->payments()->create([
                    'payment_method' => $request->payment_method,
                    'amount' => $request->grand_total,
                    'payment_date' => now(),
                    'status' => 'completed',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        return view('transactions.show', compact('sale'));
    }

    /** Menampilkan struk penjualan */
    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'user', 'cashRegister.branch']);
        // Load necessary settings for the receipt
        $companyName = Setting::get('company_name', 'PayPoint');
        $companyAddress = Setting::get('company_address', 'Jl. Contoh No. 123, Kota Anda');
        $companyPhone = Setting::get('company_phone', '(021) 12345678');
        $receiptFooter = Setting::get('receipt_footer', 'Terima kasih telah berbelanja!');

        return view('transactions.receipt', compact(
            'sale',
            'companyName',
            'companyAddress',
            'companyPhone',
            'receiptFooter'
        ));
    }

    /** Menampilkan transaksi yang ditahan (held transactions) */
    public function held()
    {
        // Asumsi ada status 'held' untuk transaksi yang ditahan
        $heldSales = Sale::where('status', 'held')->with(['customer', 'user'])->latest()->paginate(20);
        return view('transactions.held', compact('heldSales'));
    }

}
