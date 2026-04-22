<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->boolean('abrir_processo_automaticamente')->default(false)->after('permite_resposta');
            $table->string('tipo_processo_codigo')->nullable()->after('abrir_processo_automaticamente');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn(['abrir_processo_automaticamente', 'tipo_processo_codigo']);
        });
    }
};
