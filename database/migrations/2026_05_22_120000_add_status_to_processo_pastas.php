<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->string('status', 20)->default('ativo')->after('protegida');
            $table->text('motivo_parada')->nullable()->after('status');
            $table->timestamp('data_parada')->nullable()->after('motivo_parada');
            $table->unsignedBigInteger('usuario_parada_id')->nullable()->after('data_parada');
            $table->integer('tempo_total_parado_segundos')->default(0)->after('usuario_parada_id');

            $table->foreign('usuario_parada_id')->references('id')->on('usuarios_internos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->dropForeign(['usuario_parada_id']);
            $table->dropColumn(['status', 'motivo_parada', 'data_parada', 'usuario_parada_id', 'tempo_total_parado_segundos']);
        });
    }
};
