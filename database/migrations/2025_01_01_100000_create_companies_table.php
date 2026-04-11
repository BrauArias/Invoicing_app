<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('nif', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 2)->default('ES');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('logo_path')->nullable();

            // Facturación
            $table->string('invoice_series', 5)->default('F');
            $table->string('rectification_series', 5)->default('R');
            $table->string('quote_series', 5)->default('P');
            $table->unsignedInteger('invoice_counter')->default(1);
            $table->unsignedSmallInteger('invoice_year')->default(0);

            // Impuestos
            $table->decimal('default_vat_rate', 5, 2)->default(21.00);
            $table->boolean('irpf_applicable')->default(true);
            $table->decimal('irpf_rate', 5, 2)->default(15.00);

            // Personalización PDF
            $table->enum('invoice_template', ['classic', 'modern', 'minimal'])->default('classic');
            $table->string('primary_color', 7)->default('#1e3a5f');
            $table->string('accent_color', 7)->default('#d4a017');
            $table->text('invoice_footer_text')->nullable();
            $table->text('invoice_header_notes')->nullable();
            $table->boolean('show_bank_details')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
