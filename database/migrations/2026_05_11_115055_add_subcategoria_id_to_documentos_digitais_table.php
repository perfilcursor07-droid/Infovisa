<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Persiste a subcategoria escolhida no momento da criação do documento digital.
     */
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->foreignId('subcategoria_id')
                ->nullable()
                ->after('tipo_documento_id')
                ->constrained('tipo_documento_subcategorias')
                ->nullOnDelete();

            $table->index('subcategoria_id');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategoria_id');
        });
    }
};
