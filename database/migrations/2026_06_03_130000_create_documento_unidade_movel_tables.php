<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_unidade_movel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_documento_obrigatorio_id');
            $table->enum('escopo', ['geral', 'por_municipio'])->default('geral')
                ->comment('geral = pedido 1 vez na raiz; por_municipio = pedido em cada pasta/municipio');
            $table->boolean('obrigatorio')->default(true);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->foreign('tipo_documento_obrigatorio_id', 'doc_um_tipo_doc_fk')
                ->references('id')->on('tipos_documento_obrigatorio')
                ->onDelete('cascade');
        });

        Schema::create('documento_unidade_movel_cnae', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_unidade_movel_id');
            $table->string('cnae_codigo', 20);

            $table->foreign('documento_unidade_movel_id', 'doc_um_cnae_fk')
                ->references('id')->on('documento_unidade_movel')
                ->onDelete('cascade');

            $table->index('cnae_codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_unidade_movel_cnae');
        Schema::dropIfExists('documento_unidade_movel');
    }
};
