<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('min_stock');
            $table->boolean('has_expiry')->default(false)->after('expired_date');
        });
    }
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['expired_date', 'has_expiry']);
        });
    }
};
