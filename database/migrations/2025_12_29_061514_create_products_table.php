<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(16.0);
            $table->string('unit');
            $table->string('packaging');
            $table->integer('min_order_quantity')->default(1);
            $table->integer('reorder_level')->default(10);
            $table->boolean('is_active')->default(true);
            $table->json('specifications')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sku', 'is_active']);
            $table->index(['category_id', 'subcategory_id']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['name', 'description']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};