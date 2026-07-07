<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Municípios de atuação (P4) de um PJ Unidade Móvel. Cada linha guarda
     * o período de atuação e a competência (estadual/municipal) já resolvida
     * pela pactuação no momento do cadastro, além da flag usa_infovisa do
     * município para decidir entre criar processo municipal ou exibir aviso.
     */
    public function up(): void
    {
        if (Schema::hasTable('estabelecimento_municipios_atuacao')) {
            return;
        }

        Schema::create('estabelecimento_municipios_atuacao', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estabelecimento_id')
                ->constrained('estabelecimentos')
                ->cascadeOnDelete();

            $table->foreignId('municipio_id')
                ->nullable()
                ->constrained('municipios')
                ->nullOnDelete();

            $table->string('municipio_nome');
            $table->date('data_inicio');
            $table->date('data_fim');

            $table->string('competencia')->nullable()
                ->comment('estadual | municipal — resolvida pela pactuação no cadastro');

            $table->boolean('usa_infovisa')->default(false)
                ->comment('Snapshot da flag usa_infovisa do município no momento do cadastro');

            $table->string('status')->default('pendente')
                ->comment('pendente | aprovado — controle do fluxo de atuação por município');

            $table->timestamps();

            $table->index('estabelecimento_id');
            $table->index('municipio_id');
            $table->index('competencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimento_municipios_atuacao');
    }
};
