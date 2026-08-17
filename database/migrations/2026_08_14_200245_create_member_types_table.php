<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Silver, Gold
            $table->decimal('discount_percentage', 5, 2)->default(0); // 0-100
            $table->decimal('minimum_spend', 15, 2)->default(0); // minimum total sales amount to reach this level
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default member types based on the old enums
        DB::table('member_types')->insert([
            ['name' => 'Regular', 'discount_percentage' => 0, 'minimum_spend' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Silver', 'discount_percentage' => 5, 'minimum_spend' => 1000000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gold', 'discount_percentage' => 10, 'minimum_spend' => 5000000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Platinum', 'discount_percentage' => 15, 'minimum_spend' => 10000000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('member_types');
    }
};
