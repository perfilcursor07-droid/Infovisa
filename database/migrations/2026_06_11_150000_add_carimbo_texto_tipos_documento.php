<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->text('carimbo_texto')->nullable()->after('carimbo_modo')
                ->comment('Texto/template do carimbo manual. Aceita variáveis {usuario}, {data}, {processo}');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropColumn('carimbo_texto');
        });
    }
};
