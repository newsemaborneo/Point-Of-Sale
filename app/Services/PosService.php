<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosService
{
    /**
     * Mendapatkan data inisialisasi untuk halaman POS Kasir.
     */
    public function getPosData(User $user): array
    {
        $cashRegister = CashRegister::where('user_id', $user->id)->where('status', 'open')->first();
        $products     = Product::where('is_active', true)->orderBy('name')->get();
        $customers    = Customer::orderBy('name')->get();
        $categories   = Category::orderBy('name')->get();

        $warehouseId = null;
        if ($cashRegister && $cashRegister->warehouse_id) {
            $warehouseId = $cashRegister->warehouse_id;
        } elseif ($user->branch_id) {
            $userBranch = $user->branch;
            if ($userBranch && $userBranch->warehouses->isNotEmpty()) {
                $warehouseId = $userBranch->warehouses->first()->id;
            }
        }
        $warehouseId = $warehouseId ?? 1;

        $activePromotions = Promotion::where('is_active', true)->with('products')->get();
        $activeVouchers   = Voucher::where('end_date', '>=', now())->orWhereNull('end_date')->get();

        $productsJson = $products->map(fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'price'       => (float) $p->sale_price,
            'stock'       => (int) $p->stockInWarehouse($warehouseId),
            'photo'       => $p->photo ?? null,
            'category_id' => $p->category_id,
        ])->values()->toArray();

        $storeHours = [
            'enabled' => (bool) Setting::get('store_hours_enabled', false),
            'open'    => Setting::get('store_open_time', '08:00'),
            'close'   => Setting::get('store_close_time', '21:00'),
        ];

        return [
            'productsJson'     => $productsJson,
            'customers'        => $customers,
            'cashRegister'     => $cashRegister,
            'categories'       => $categories,
            'storeHours'       => $storeHours,
            'activePromotions' => $activePromotions,
            'activeVouchers'   => $activeVouchers,
        ];
    }

    /**
     * Memproses transaksi penjualan POS.
     */
    public function processSale(array $data, User $user): array
    {
        $cashRegister = CashRegister::where('user_id', $user->id)->where('status', 'open')->first();

        if (!$cashRegister) {
            return [
                'success' => false,
                'status_code' => 403,
                'message' => 'Tidak dapat memproses transaksi. Shift kasir belum dibuka.'
            ];
        }

        // Cek jam operasional toko
        $storeHoursEnabled = (bool) Setting::get('store_hours_enabled', false);
        $storeLocalTimezone = 'Asia/Jakarta';

        if ($storeHoursEnabled) {
            $storeOpenTime  = Setting::get('store_open_time', '08:00');
            $storeCloseTime = Setting::get('store_close_time', '21:00');
            $now = now();

            $openTimeLocal  = Carbon::createFromTimeString($storeOpenTime, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);
            $closeTimeLocal = Carbon::createFromTimeString($storeCloseTime, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);

            $openTimeApp  = $openTimeLocal->copy()->timezone($now->timezone);
            $closeTimeApp = $closeTimeLocal->copy()->timezone($now->timezone);

            $storeIsOpen = false;
            if ($closeTimeApp->lessThan($openTimeApp)) {
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeApp) || $now->lessThan($closeTimeApp);
            } else {
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeApp) && $now->lessThan($closeTimeApp);
            }

            if (!$storeIsOpen) {
                return [
                    'success' => false,
                    'status_code' => 403,
                    'message' => 'Tidak dapat memproses transaksi. Toko sedang tutup. Jam operasional: ' . $storeOpenTime . ' - ' . $storeCloseTime . '.'
                ];
            }
        }

        $warehouseId = $cashRegister->warehouse_id ?? 1;

        try {
            $sale = DB::transaction(function () use ($data, $user, $cashRegister, $warehouseId) {
                $sale = Sale::create([
                    'invoice_number'   => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                    'customer_id'      => $data['customer_id'] ?? null,
                    'user_id'          => $user->id,
                    'cash_register_id' => $cashRegister->id,
                    'subtotal'         => $data['subtotal'],
                    'warehouse_id'     => $warehouseId,
                    'branch_id'        => $cashRegister->branch_id ?? $user->branch_id,
                    'discount_total'   => $data['discount_total'] ?? 0,
                    'tax_total'        => 0,
                    'grand_total'      => $data['grand_total'],
                    'paid_amount'      => $data['paid_amount'],
                    'change_amount'    => max(0, $data['paid_amount'] - $data['grand_total']),
                    'status'           => 'completed',
                ]);

                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    $costPrice = $product ? $product->purchase_price : 0;
                    $subtotal = $item['quantity'] * $item['price'];

                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                        'cost_price' => $costPrice,
                        'discount'   => 0,
                        'tax'        => 0,
                        'subtotal'   => $subtotal,
                    ]);

                    $stock = ProductStock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $warehouseId],
                        ['quantity' => 0]
                    );

                    $quantityBefore = $stock->quantity;
                    $stock->decrement('quantity', $item['quantity']);

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
                        'user_id'         => $user->id,
                    ]);
                }

                if (method_exists($sale, 'payments')) {
                    $sale->payments()->create([
                        'payment_method' => $data['payment_method'],
                        'amount'         => $data['grand_total'],
                        'payment_date'   => now(),
                        'status'         => 'completed',
                    ]);
                }

                return $sale;
            });

            return [
                'success'        => true,
                'status_code'    => 200,
                'message'        => 'Transaksi berhasil!',
                'sale_id'        => $sale->id,
                'invoice_number' => $sale->invoice_number,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing POS Sale: ' . $e->getMessage());
            return [
                'success'     => false,
                'status_code' => 500,
                'message'     => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Menyiapkan data struk transaksi.
     */
    public function getReceiptData(Sale $sale): array
    {
        $sale->load(['customer', 'items.product', 'user', 'cashRegister.branch']);

        return [
            'sale'           => $sale,
            'companyName'    => Setting::get('company_name', 'PayPoint'),
            'companyAddress' => Setting::get('company_address', 'Jl. Contoh No. 123, Kota Anda'),
            'companyPhone'   => Setting::get('company_phone', '(021) 12345678'),
            'receiptFooter'  => Setting::get('receipt_footer', 'Terima kasih telah berbelanja!'),
        ];
    }
}
