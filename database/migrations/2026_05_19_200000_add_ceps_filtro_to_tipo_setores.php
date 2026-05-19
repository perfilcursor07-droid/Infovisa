<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campo ceps_filtro na tabela tipo_setores.
 * 
 * Quando preenchido, usuários vinculados a esse setor verão apenas
 * estabelecimentos/processos cujo CEP comece com um dos prefixos listados.
 * 
 * Exemplo: setor "Luzimangues" com ceps_filtro = ["77502"] verá apenas
 * estabelecimentos com CEP 77502xxx, mesmo estando no município Porto Nacional.
 * 
 * Quando NULL, o comportamento padrão é mantido (vê tudo do município).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->jsonb('ceps_filtro')->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->dropColumn('ceps_filtro');
        });
    }
};
