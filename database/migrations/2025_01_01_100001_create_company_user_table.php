<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'user', 'viewer'])->default('owner');
            $table->unique(['company_id', 'user_id']);
            $table->timestamps();
        });

        // Add FK for active_company_id now that companies table exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('active_company_id')
                  ->references('id')->on('companies')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_company_id']);
        });
        Schema::dropIfExists('company_user');
    }
};
