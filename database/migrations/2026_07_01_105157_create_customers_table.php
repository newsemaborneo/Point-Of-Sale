<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('member_type', ['regular', 'silver', 'gold', 'platinum'])->default('regular');
            $table->string('member_code')->unique()->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
