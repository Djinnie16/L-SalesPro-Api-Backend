<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->enum('type', ['Garage', 'Dealership', 'Individual', 'Corporate']);
            $table->enum('category', ['A+', 'A', 'B', 'C'])->default('C');
            $table->string('contact_person');
            $table->string('phone');
            $table->string('email');
            $table->string('tax_id')->nullable();
            $table->integer('payment_terms')->default(30); // Days
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('available_credit')->virtualAs('credit_limit - current_balance');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('address');
            $table->string('territory')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'category', 'is_active']);
            $table->index(['credit_limit', 'current_balance']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};