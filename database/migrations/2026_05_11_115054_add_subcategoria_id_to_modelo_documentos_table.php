<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Vincula (opcionalmente) um modelo de documento a uma subcategoria do tipo de documento.
     */
    public function up(): void
    {
        Schema::table('modelo_documentos', function (Blueprint $table) {
            $table->foreignId('subcategoria_id')
                ->nullable()
                ->after('tipo_documento_id')
                ->constrained('tipo_documento_subcategorias')
                ->nullOnDelete();

            $table->index(['tipo_documento_id', 'subcategoria_id']);
        });
    }

    public function down(): void
    {
        Schema::table('modelo_documentos', function (Blueprint $table) {
            $table->dropIndex(['tipo_documento_id', 'subcategoria_id']);
            $table->dropConstrainedForeignId('subcategoria_id');
        });
    }
};
