<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();

            // Numeración
            $table->string('series', 5);
            $table->unsignedInteger('number');
            $table->string('full_number', 20)->unique();
            $table->enum('type', ['invoice', 'proforma', 'quote', 'credit_note'])->default('invoice');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');

            // Fechas
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('service_date')->nullable();

            // Snapshot del cliente (guardado al emitir)
            $table->string('client_name');
            $table->string('client_nif', 20)->nullable();
            $table->string('client_address')->nullable();
            $table->string('client_city')->nullable();
            $table->string('client_postal_code', 10)->nullable();
            $table->string('client_country', 2)->nullable();

            // Snapshot de la empresa (incluyendo config de plantilla)
            $table->json('company_snapshot')->nullable();

            // Totales
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('irpf_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('vat_breakdown')->nullable();

            // Pago
            $table->string('payment_method')->nullable();
            $table->string('payment_terms')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Notas
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('currency', 3)->default('EUR');

            // PDF generado
            $table->string('pdf_path')->nullable();

            // Factura relacionada (para rectificativas)
            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'issue_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
