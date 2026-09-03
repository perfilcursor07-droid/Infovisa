<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->boolean('exige_itens_atendimento')
                ->default(false)
                ->after('permite_resposta');
        });

        Schema::create('documento_itens_atendimento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')
                ->constrained('documentos_digitais')
                ->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(1);
            $table->text('descricao');
            $table->text('embasamento_legal')->nullable();
            $table->timestamps();

            $table->index(['documento_digital_id', 'ordem']);
        });

        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->foreignId('documento_item_atendimento_id')
                ->nullable()
                ->after('documento_digital_id')
                ->constrained('documento_itens_atendimento')
                ->cascadeOnDelete();
            $table->index(['documento_item_atendimento_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->dropForeign(['documento_item_atendimento_id']);
            $table->dropIndex(['documento_item_atendimento_id', 'status']);
            $table->dropColumn('documento_item_atendimento_id');
        });

        Schema::dropIfExists('documento_itens_atendimento');

        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn('exige_itens_atendimento');
        });
    }
};
