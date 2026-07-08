<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percent_discount', 'nominal_discount', 'buy_x_get_y', 'bundling', 'happy_hour']);
            $table->decimal('value', 15, 2)->nullable(); // discount value
            $table->integer('buy_qty')->nullable();
            $table->integer('get_qty')->nullable();
            $table->time('start_time')->nullable(); // for happy hour
            $table->time('end_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });

        // Bundling: group of products sold as one package
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('bundle_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
