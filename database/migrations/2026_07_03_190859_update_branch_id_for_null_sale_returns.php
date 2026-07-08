<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahkan ini untuk menggunakan DB facade
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perbarui sale_returns yang memiliki branch_id NULL
        // dengan mengambil branch_id dari sale yang terkait
        DB::table('sale_returns')
            ->whereNull('branch_id')
            ->chunkById(100, function ($saleReturns) {
                foreach ($saleReturns as $saleReturn) {
                    $sale = DB::table('sales')->find($saleReturn->sale_id);
                    if ($sale && $sale->branch_id !== null) {
                        DB::table('sale_returns')
                            ->where('id', $saleReturn->id)
                            ->update(['branch_id' => $sale->branch_id]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu melakukan rollback untuk perubahan data ini
        // karena ini adalah perbaikan data yang spesifik.
        // Jika Anda benar-benar perlu, Anda harus memiliki backup database.
    }
};
