<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca quais atividades (CNAEs) da pactuação são permitidas no cadastro
     * de Pessoa Física. Aditivo e isolado: não afeta a lógica de competência
     * existente nem os demais módulos.
     */
    public function up(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->boolean('pessoa_fisica')->default(false)->after('unidade_movel')
                ->comment('Indica se o CNAE é permitido no cadastro de Pessoa Física');
            $table->index('pessoa_fisica');
        });
    }

    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropIndex(['pessoa_fisica']);
            $table->dropColumn('pessoa_fisica');
        });
    }
};
