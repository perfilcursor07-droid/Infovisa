<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->index('responsavel_atual_id', 'processos_responsavel_atual_id_idx');
            $table->index('setor_atual', 'processos_setor_atual_idx');
            $table->index('created_at', 'processos_created_at_idx');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->index(['status_aprovacao', 'tipo_usuario', 'processo_id'], 'processo_documentos_status_tipo_processo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropIndex('processos_responsavel_atual_id_idx');
            $table->dropIndex('processos_setor_atual_idx');
            $table->dropIndex('processos_created_at_idx');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropIndex('processo_documentos_status_tipo_processo_idx');
        });
    }
};
