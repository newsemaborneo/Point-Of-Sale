<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\Branch; // Pastikan model Branch sudah di-import
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahkan ini
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan dashboard: pendapatan hari ini, jumlah transaksi,
     * produk terlaris, grafik penjualan, notifikasi stok menipis.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userBranchId = $user->branch_id;
        $isAdminOrSupervisor = $user->hasRole('admin') || $user->hasRole('supervisor');

        $userBranchName = null;
        if ($userBranchId) {
            $userBranch = Branch::find($userBranchId);
            if ($userBranch) {
                $userBranchName = $userBranch->name;
            }
        }
        // Dapatkan pengaturan jam operasional toko
        $storeHoursEnabled = (bool) Setting::get('store_hours_enabled', false);
        $storeOpenTimeStr  = Setting::get('store_open_time',  '08:00');
        $storeCloseTimeStr = Setting::get('store_close_time', '21:00');

        $now = now();

        // ===== Tentukan periode perhitungan "hari ini" =====
        // Periode ini dihitung sejak JAM BUKA TERAKHIR yang sudah lewat,
        // dan baru akan reset saat memasuki jam buka berikutnya.
        // Jam tutup TIDAK mempengaruhi reset ini.
        if ($storeHoursEnabled) {
            $openTimeCarbon = Carbon::createFromTimeString($storeOpenTimeStr);
            $todayOpenTime  = $now->copy()->setTime($openTimeCarbon->hour, $openTimeCarbon->minute, 0);

            if ($now->greaterThanOrEqualTo($todayOpenTime)) {
                // Sudah melewati jam buka hari ini → mulai dari jam buka hari ini
                $calculationStartTime = $todayOpenTime;
            } else {
                // Belum masuk jam buka hari ini → berarti masih dalam periode
                // sejak jam buka KEMARIN (belum reset)
                $calculationStartTime = $todayOpenTime->copy()->subDay();
            }
        } else {
            // Tanpa jam operasional, anggap reset mengikuti pergantian hari kalender
            $calculationStartTime = $now->copy()->startOfDay();
        }

        Log::info('Dashboard Calculation Start Time: ' . $calculationStartTime->toDateTimeString());
        Log::info('Logged in User Branch ID: ' . ($userBranchId ?? 'N/A'));
        Log::info('Current Time: ' . $now->toDateTimeString());
        Log::info('Store Hours Enabled: ' . ($storeHoursEnabled ? 'Yes' : 'No'));
        if ($storeHoursEnabled) Log::info('Store Open/Close Time: ' . $storeOpenTimeStr . ' - ' . $storeCloseTimeStr);

        // Tentukan zona waktu lokal toko.
        // Untuk saat ini, kita akan mengasumsikan 'Asia/Jakarta' sebagai zona waktu lokal umum untuk pengguna di Indonesia.
        // Dalam sistem yang lebih kuat, ini harus dapat dikonfigurasi per cabang atau sebagai pengaturan global.
        $storeLocalTimezone = 'Asia/Jakarta'; // Placeholder, idealnya dari pengaturan atau konfigurasi cabang

        // Set locale Carbon ke Indonesia agar nama hari/bulan tampil dalam Bahasa Indonesia
        Carbon::setLocale('id');

        // ===== Status buka/tutup toko (opsional, untuk tampilan badge dsb) =====
        $storeIsOpen = true;
        if ($storeHoursEnabled) {
            // Buat instance Carbon untuk jam buka dan tutup hari ini dalam zona waktu lokal toko
            $openTimeLocal  = Carbon::createFromTimeString($storeOpenTimeStr, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);
            $closeTimeLocal = Carbon::createFromTimeString($storeCloseTimeStr, $storeLocalTimezone)->setDate($now->year, $now->month, $now->day);

            // Konversi waktu lokal ini ke zona waktu aplikasi untuk perbandingan yang akurat dengan $now
            $openTimeAppTimezone  = $openTimeLocal->copy()->timezone($now->timezone);
            $closeTimeAppTimezone = $closeTimeLocal->copy()->timezone($now->timezone);

            if ($now->greaterThanOrEqualTo($openTimeAppTimezone)) {
                $calculationStartTime = $openTimeAppTimezone;
            } else {
                $calculationStartTime = $openTimeAppTimezone->copy()->subDay();
            }

            if ($closeTimeAppTimezone->lessThan($openTimeAppTimezone)) {
                // Shift semalaman (overnight)
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeAppTimezone) || $now->lessThan($closeTimeAppTimezone);
            } else {
                $storeIsOpen = $now->greaterThanOrEqualTo($openTimeAppTimezone) && $now->lessThan($closeTimeAppTimezone);
            }
        }

        // ===== Hitung pendapatan & transaksi berdasarkan periode, bukan status buka =====
        $todaySales = Sale::where('created_at', '>=', $calculationStartTime)
            ->where('status', 'completed');

        if (!$isAdminOrSupervisor && $userBranchId) {
            $todaySales->where('branch_id', $userBranchId);
        }

        Log::info('Today Sales Query: ' . $todaySales->toSql() . ' - Bindings: ' . json_encode($todaySales->getBindings()));

        $totalRevenue = (clone $todaySales)->sum('grand_total');
        $totalTransactions = (clone $todaySales)->count();

        $bestSellers = SaleItem::selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('sale', function ($q) use ($calculationStartTime, $isAdminOrSupervisor, $userBranchId) {
                $q->where('created_at', '>=', $calculationStartTime)->where('status', 'completed');
                if (!$isAdminOrSupervisor && $userBranchId) {
                    $q->where('branch_id', $userBranchId);
                }
            })
            ->tap(function ($query) {
                Log::info('Best Sellers Query: ' . $query->toSql() . ' - Bindings: ' . json_encode($query->getBindings()));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(5)
            ->get();

        // ===== Rentang tanggal untuk grafik penjualan (7 hari, zona waktu lokal toko) =====
        $nowInStoreTimezone = Carbon::now($storeLocalTimezone);
        $chartStartDateLocal = $nowInStoreTimezone->copy()->subDays(6)->startOfDay();
        $chartEndDateLocal = $nowInStoreTimezone->copy()->endOfDay();

        $chartStartDateUtc = $chartStartDateLocal->copy()->setTimezone('UTC');
        $chartEndDateUtc = $chartEndDateLocal->copy()->setTimezone('UTC');

        // Label 7 hari (dipakai untuk sumbu X grafik, sama untuk semua dataset/cabang)
        $salesChartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateLocal = $nowInStoreTimezone->copy()->subDays($i);
            $salesChartLabels[] = $dateLocal->translatedFormat('D, d M');
        }

        // Palet warna untuk membedakan tiap cabang di grafik
        $branchColorPalette = [
            '#6366f1', // indigo
            '#10b981', // emerald
            '#f59e0b', // amber
            '#ef4444', // rose
            '#0ea5e9', // sky
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#14b8a6', // teal
        ];

        /**
         * Helper: ambil data penjualan mentah (created_at, grand_total, branch_id)
         * lalu kelompokkan per tanggal LOKAL toko menggunakan PHP/Carbon.
         * (Tidak pakai CONVERT_TZ() di query karena butuh tabel timezone MySQL
         * yang sering belum ter-load, sehingga bisa mengembalikan NULL.)
         */
        $groupSalesByLocalDate = function ($salesCollection) use ($storeLocalTimezone) {
            $grouped = [];
            foreach ($salesCollection as $sale) {
                $localDate = Carbon::parse($sale->created_at)
                    ->timezone($storeLocalTimezone)
                    ->format('Y-m-d');

                $grouped[$localDate] = ($grouped[$localDate] ?? 0) + (float) $sale->grand_total;
            }
            return $grouped;
        };

        $salesChartDatasets = [];

        if ($isAdminOrSupervisor) {
            // ===== Admin/Supervisor: pecah grafik per cabang =====
            $branches = Branch::orderBy('name')->get();

            foreach ($branches as $index => $branch) {
                $branchSalesQuery = Sale::select('created_at', 'grand_total')
                    ->where('status', 'completed')
                    ->where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$chartStartDateUtc, $chartEndDateUtc]);

                Log::info("Sales Chart Query (Branch: {$branch->name}): " . $branchSalesQuery->toSql() . ' - Bindings: ' . json_encode($branchSalesQuery->getBindings()));

                $branchSales = $branchSalesQuery->get();
                $branchSalesByDate = $groupSalesByLocalDate($branchSales);

                $branchData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $dateKey = $nowInStoreTimezone->copy()->subDays($i)->format('Y-m-d');
                    $branchData[] = $branchSalesByDate[$dateKey] ?? 0;
                }

                // Lewati cabang yang tidak punya transaksi sama sekali dalam 7 hari,
                // supaya legend grafik tidak penuh garis kosong. Hapus baris di bawah
                // jika ingin tetap menampilkan semua cabang meski datanya 0.
                if (array_sum($branchData) <= 0) {
                    continue;
                }

                $salesChartDatasets[] = [
                    'label' => $branch->name,
                    'data'  => $branchData,
                    'color' => $branchColorPalette[$index % count($branchColorPalette)],
                ];
            }

            // Data gabungan semua cabang (dipakai untuk variabel lama $salesChart bila masih dipakai di tempat lain)
            $salesChartData = array_fill(0, 7, 0);
            foreach ($salesChartDatasets as $dataset) {
                foreach ($dataset['data'] as $i => $value) {
                    $salesChartData[$i] += $value;
                }
            }
        } else {
            // ===== User biasa (1 cabang): satu dataset saja =====
            $salesChartQuery = Sale::select('created_at', 'grand_total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$chartStartDateUtc, $chartEndDateUtc]);

            if ($userBranchId) {
                $salesChartQuery->where('branch_id', $userBranchId);
            }

            Log::info('Sales Chart Date Range (Local): ' . $chartStartDateLocal->toDateTimeString() . ' to ' . $chartEndDateLocal->toDateTimeString());
            Log::info('Sales Chart Date Range (UTC for DB): ' . $chartStartDateUtc->toDateTimeString() . ' to ' . $chartEndDateUtc->toDateTimeString());
            Log::info('Sales Chart Query: ' . $salesChartQuery->toSql() . ' - Bindings: ' . json_encode($salesChartQuery->getBindings()));

            $rawSales = $salesChartQuery->get();
            Log::info('Sales Chart Raw Rows Count: ' . $rawSales->count());

            $salesByDate = $groupSalesByLocalDate($rawSales);

            $salesChartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateKey = $nowInStoreTimezone->copy()->subDays($i)->format('Y-m-d');
                $salesChartData[] = $salesByDate[$dateKey] ?? 0;
            }

            $salesChartDatasets[] = [
                'label' => $userBranchName ?? 'Cabang Saya',
                'data'  => $salesChartData,
                'color' => $branchColorPalette[0],
            ];
        }

        // Kompatibilitas dengan variabel lama $salesChart (koleksi tanggal => total),
        // jika masih dipakai di view/tempat lain.
        $salesChart = collect($salesChartLabels)->map(function ($label, $index) use ($salesChartData) {
            return (object) ['date' => $label, 'total' => $salesChartData[$index] ?? 0];
        })->values();

        Log::info('Sales Chart Labels: ' . json_encode($salesChartLabels));
        Log::info('Sales Chart Datasets: ' . json_encode($salesChartDatasets));

        $lowStockProducts = collect();
        if (!$isAdminOrSupervisor && $userBranchId) {
            $warehouseIdsInBranch = Warehouse::where('branch_id', $userBranchId)->pluck('id');

            $lowStockProducts = Product::select('products.*') // Select all product columns
                ->joinSub(function ($query) use ($warehouseIdsInBranch) {
                    $query->from('product_stocks')
                          ->select('product_id', DB::raw('SUM(quantity) as total_warehouse_stock'))
                          ->whereIn('warehouse_id', $warehouseIdsInBranch)
                          ->groupBy('product_id');
                }, 'warehouse_stocks', function ($join) {
                    $join->on('products.id', '=', 'warehouse_stocks.product_id');
                })
                ->whereColumn('warehouse_stocks.total_warehouse_stock', '<=', 'products.min_stock')
                ->with(['stocks' => fn($q) => $q->whereIn('warehouse_id', $warehouseIdsInBranch)])
                ->get();
        } else {
            $lowStockProducts = Product::with('stocks')
                ->get()
                ->filter(fn (Product $p) => $p->isLowStock())
                ->values();
        }

        return view('dashboard.index', compact(
            'totalRevenue',
            'totalTransactions',
            'bestSellers',
            'salesChart',
            'salesChartLabels',
            'salesChartData',
            'salesChartDatasets', // <-- variabel baru yang dibutuhkan oleh view
            'lowStockProducts',
            'storeIsOpen',
            'userBranchName'
        ));
    }
}