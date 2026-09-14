<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_itens_atendimento', function (Blueprint $table) {
            $table->string('area')->nullable()->after('ordem');
        });
    }

    public function down(): void
    {
        Schema::table('documento_itens_atendimento', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
