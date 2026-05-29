<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->string('gestor_assinatura_hash')->nullable()->after('gestor_assinatura_id');
            $table->timestamp('gestor_assinado_em')->nullable()->after('gestor_assinatura_hash');
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn(['gestor_assinatura_hash', 'gestor_assinado_em']);
        });
    }
};
