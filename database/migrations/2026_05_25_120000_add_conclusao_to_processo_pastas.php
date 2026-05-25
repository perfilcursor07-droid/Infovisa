<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->timestamp('data_conclusao')->nullable()->after('tempo_total_parado_segundos');
            $table->text('motivo_conclusao')->nullable()->after('data_conclusao');
            $table->unsignedBigInteger('usuario_conclusao_id')->nullable()->after('motivo_conclusao');

            $table->foreign('usuario_conclusao_id')->references('id')->on('usuarios_internos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->dropForeign(['usuario_conclusao_id']);
            $table->dropColumn(['data_conclusao', 'motivo_conclusao', 'usuario_conclusao_id']);
        });
    }
};
