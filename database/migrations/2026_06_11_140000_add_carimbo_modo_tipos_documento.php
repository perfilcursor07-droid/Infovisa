<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            if (!Schema::hasColumn('tipos_documento_obrigatorio', 'carimbo_modo')) {
                $table->string('carimbo_modo', 20)->default('desativado')->after('carimbar_aprovacao')
                    ->comment('Modo do carimbo de validação: desativado, automatico ou manual');
            }
        });

        // Migra a configuração antiga: carimbar_aprovacao = true vira modo automático
        DB::table('tipos_documento_obrigatorio')
            ->where('carimbar_aprovacao', true)
            ->update(['carimbo_modo' => 'automatico']);
    }

    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropColumn('carimbo_modo');
        });
    }
};
