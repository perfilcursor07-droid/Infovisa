<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->boolean('carimbar_aprovacao')->default(false)->after('prazo_validade_dias')
                ->comment('Se true, ao aprovar o documento o sistema carimba o PDF com dados de verificação e QR Code');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->string('codigo_validacao', 64)->nullable()->unique()->after('aprovado_em')
                ->comment('Código público de validação do documento aprovado (QR Code)');
            $table->string('caminho_carimbado')->nullable()->after('codigo_validacao')
                ->comment('Caminho do PDF carimbado com a faixa de validação');
            $table->string('hash_arquivo', 64)->nullable()->after('caminho_carimbado')
                ->comment('SHA-256 do arquivo original no momento da aprovação');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropColumn('carimbar_aprovacao');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropColumn(['codigo_validacao', 'caminho_carimbado', 'hash_arquivo']);
        });
    }
};
