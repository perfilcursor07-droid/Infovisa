<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status do módulo Unidade Móvel, separado do status do estabelecimento.
     * Permite que um estabelecimento já aprovado solicite o credenciamento
     * de Unidade Móvel sem afetar seu status principal.
     *
     * Valores: null (não solicitou), pendente, aprovado, rejeitado.
     */
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->string('status_unidade_movel')->nullable()->after('respostas_unidade_movel')
                ->comment('Status do módulo Unidade Móvel: null|pendente|aprovado|rejeitado');

            $table->text('motivo_rejeicao_unidade_movel')->nullable()->after('status_unidade_movel')
                ->comment('Motivo da rejeição do módulo Unidade Móvel');

            $table->index('status_unidade_movel');
        });

        // Backfill: estabelecimentos de Unidade Móvel já existentes herdam o status
        // do próprio estabelecimento para que o módulo continue funcionando.
        DB::table('estabelecimentos')
            ->where('is_unidade_movel', true)
            ->where('status', 'aprovado')
            ->update(['status_unidade_movel' => 'aprovado']);

        DB::table('estabelecimentos')
            ->where('is_unidade_movel', true)
            ->where('status', 'pendente')
            ->update(['status_unidade_movel' => 'pendente']);

        DB::table('estabelecimentos')
            ->where('is_unidade_movel', true)
            ->where('status', 'rejeitado')
            ->update(['status_unidade_movel' => 'rejeitado']);
    }

    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropIndex(['status_unidade_movel']);
            $table->dropColumn([
                'status_unidade_movel',
                'motivo_rejeicao_unidade_movel',
            ]);
        });
    }
};
