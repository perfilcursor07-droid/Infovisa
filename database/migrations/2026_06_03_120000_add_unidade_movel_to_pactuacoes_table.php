<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Marca quais atividades (CNAEs) da pactuação são contempladas para o
     * cadastro de PJ Unidade Móvel. Aditivo e isolado: não afeta a lógica
     * de competência existente.
     */
    public function up(): void
    {
        if (Schema::hasColumn('pactuacoes', 'unidade_movel')) {
            return;
        }

        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->boolean('unidade_movel')->default(false)->after('tabela')
                ->comment('Indica se o CNAE é contemplado para o cadastro de PJ Unidade Móvel');
            $table->index('unidade_movel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropIndex(['unidade_movel']);
            $table->dropColumn('unidade_movel');
        });
    }
};
