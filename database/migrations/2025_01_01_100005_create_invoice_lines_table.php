<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('description');
            $table->decimal('quantity', 10, 3)->default(1);
            $table->string('unit', 30)->default('servicio');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('vat_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
