<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Campos do módulo PJ Unidade Móvel. São aditivos e isolados: o
     * comportamento novo só é acionado quando is_unidade_movel = true,
     * sem afetar os cadastros juridica/fisica existentes.
     */
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->boolean('is_unidade_movel')->default(false)->after('tipo_pessoa')
                ->comment('Indica se o cadastro é um PJ Unidade Móvel (serviço itinerante de outro estado)');

            $table->string('tipo_unidade_movel')->nullable()->after('is_unidade_movel')
                ->comment('Tipo da unidade móvel: UTI Móvel / Carreta / Van / Outro');

            $table->jsonb('respostas_unidade_movel')->nullable()->after('tipo_unidade_movel')
                ->comment('Respostas do questionário da unidade móvel (P1, P2) e metadados');

            $table->index('is_unidade_movel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropIndex(['is_unidade_movel']);
            $table->dropColumn([
                'is_unidade_movel',
                'tipo_unidade_movel',
                'respostas_unidade_movel',
            ]);
        });
    }
};
