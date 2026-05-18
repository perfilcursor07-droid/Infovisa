<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos para suporte a documentos físicos (auto de infração entregue em loco).
 * 
 * - tipo_origem: 'digital' (padrão) ou 'fisico'
 * - arquivo_fisico_pdf: caminho do PDF escaneado do documento físico
 * - data_entrega_fisica: data em que o documento foi entregue ao estabelecimento
 *   (o prazo conta a partir do dia útil seguinte a essa data)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->string('tipo_origem', 15)->default('digital')->after('status');
            $table->string('arquivo_fisico_pdf')->nullable()->after('tipo_origem');
            $table->date('data_entrega_fisica')->nullable()->after('arquivo_fisico_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropColumn(['tipo_origem', 'arquivo_fisico_pdf', 'data_entrega_fisica']);
        });
    }
};
