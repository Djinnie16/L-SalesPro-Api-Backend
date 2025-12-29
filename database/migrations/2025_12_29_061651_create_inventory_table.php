<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->virtualAs('quantity - reserved_quantity');
            $table->decimal('average_cost', 12, 2)->nullable();
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['product_id', 'warehouse_id']);
            $table->index(['warehouse_id', 'available_quantity']);
            $table->index('available_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};