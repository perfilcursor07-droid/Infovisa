<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->boolean('documentos_manuais')->default(false)->after('usa_infovisa')
                ->comment('Se true, a vigilância municipal define manualmente os documentos obrigatórios por estabelecimento (sem listas configuradas)');
        });

        Schema::create('estabelecimento_documento_manual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->cascadeOnDelete();
            $table->foreignId('tipo_documento_obrigatorio_id')->constrained('tipos_documento_obrigatorio')->cascadeOnDelete();
            $table->unsignedBigInteger('definido_por')->nullable();
            $table->foreign('definido_por')->references('id')->on('usuarios_internos')->nullOnDelete();
            $table->timestamps();

            $table->unique(['estabelecimento_id', 'tipo_documento_obrigatorio_id'], 'estab_doc_manual_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estabelecimento_documento_manual');

        Schema::table('municipios', function (Blueprint $table) {
            $table->dropColumn('documentos_manuais');
        });
    }
};
