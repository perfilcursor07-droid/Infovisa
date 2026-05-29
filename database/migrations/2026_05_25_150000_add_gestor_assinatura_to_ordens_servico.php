<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_assinatura_id')->nullable()->after('cancelada_por');
            $table->foreign('gestor_assinatura_id')->references('id')->on('usuarios_internos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['gestor_assinatura_id']);
            $table->dropColumn('gestor_assinatura_id');
        });
    }
};
