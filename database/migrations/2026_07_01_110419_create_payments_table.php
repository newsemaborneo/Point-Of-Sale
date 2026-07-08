<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->enum('method', ['cash', 'qris', 'debit', 'credit', 'e_wallet', 'bank_transfer']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable(); // for non-cash
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
