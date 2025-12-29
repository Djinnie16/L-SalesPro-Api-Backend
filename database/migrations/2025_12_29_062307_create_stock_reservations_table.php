<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('status', ['reserved', 'released', 'consumed'])->default('reserved');
            $table->timestamp('expires_at')->nullable(); // Auto-release after 30 minutes
            $table->timestamps();
            
            $table->index(['order_id', 'product_id']);
            $table->index(['status', 'expires_at']);
            $table->index('expires_at'); // For cleaning up expired reservations
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};