<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE processo_eventos
            DROP CONSTRAINT IF EXISTS processo_eventos_tipo_evento_check
        ");

        DB::statement("
            ALTER TABLE processo_eventos
            ADD CONSTRAINT processo_eventos_tipo_evento_check
            CHECK (tipo_evento::text = ANY (ARRAY[
                'processo_criado'::text,
                'documento_anexado'::text,
                'documento_digital_criado'::text,
                'documento_excluido'::text,
                'documento_digital_excluido'::text,
                'status_alterado'::text,
                'processo_arquivado'::text,
                'processo_desarquivado'::text,
                'processo_parado'::text,
                'processo_reiniciado'::text,
                'processo_reaberto'::text,
                'movimentacao'::text,
                'observacao_adicionada'::text,
                'resposta_aprovada'::text,
                'resposta_rejeitada'::text,
                'processo_atribuido'::text,
                'prazo_prorrogado'::text,
                'unidade_parada'::text,
                'unidade_retomada'::text,
                'pasta_parada'::text,
                'pasta_concluida'::text,
                'pasta_reaberta'::text
            ]))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE processo_eventos
            DROP CONSTRAINT IF EXISTS processo_eventos_tipo_evento_check
        ");

        DB::statement("
            ALTER TABLE processo_eventos
            ADD CONSTRAINT processo_eventos_tipo_evento_check
            CHECK (tipo_evento::text = ANY (ARRAY[
                'processo_criado'::text,
                'documento_anexado'::text,
                'documento_digital_criado'::text,
                'documento_excluido'::text,
                'documento_digital_excluido'::text,
                'status_alterado'::text,
                'processo_arquivado'::text,
                'processo_desarquivado'::text,
                'processo_parado'::text,
                'processo_reiniciado'::text,
                'movimentacao'::text,
                'observacao_adicionada'::text,
                'resposta_aprovada'::text,
                'resposta_rejeitada'::text,
                'processo_atribuido'::text,
                'prazo_prorrogado'::text,
                'unidade_parada'::text,
                'unidade_retomada'::text
            ]))
        ");
    }
};
