<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->boolean('exibir_aviso_abertura_empresa')->default(false)->after('usuario_externo_pode_abrir');
            $table->string('aviso_abertura_titulo')->nullable()->after('exibir_aviso_abertura_empresa');
            $table->text('aviso_abertura_mensagem')->nullable()->after('aviso_abertura_titulo');
            $table->string('aviso_abertura_confirmacao')->nullable()->after('aviso_abertura_mensagem');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn([
                'exibir_aviso_abertura_empresa',
                'aviso_abertura_titulo',
                'aviso_abertura_mensagem',
                'aviso_abertura_confirmacao',
            ]);
        });
    }
};
