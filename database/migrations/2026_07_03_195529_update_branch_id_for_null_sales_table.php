<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perbarui sales yang memiliki branch_id NULL
        DB::table('sales')
            ->whereNull('branch_id')
            ->chunkById(100, function ($sales) {
                foreach ($sales as $sale) {
                    $updatedBranchId = null;

                    // Coba ambil dari cash_register yang terkait
                    if ($sale->cash_register_id) {
                        $cashRegister = DB::table('cash_registers')->find($sale->cash_register_id);
                        if ($cashRegister && $cashRegister->branch_id !== null) {
                            $updatedBranchId = $cashRegister->branch_id;
                        }
                    }

                    // Jika belum ditemukan, coba ambil dari user yang terkait
                    if ($updatedBranchId === null && $sale->user_id) {
                        $user = DB::table('users')->find($sale->user_id);
                        if ($user && $user->branch_id !== null) {
                            $updatedBranchId = $user->branch_id;
                        }
                    }

                    // Jika branch_id ditemukan, perbarui sale
                    if ($updatedBranchId !== null) {
                        DB::table('sales')
                            ->where('id', $sale->id)
                            ->update(['branch_id' => $updatedBranchId]);
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
    }
};

