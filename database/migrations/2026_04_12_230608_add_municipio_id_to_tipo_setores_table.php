<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->unsignedBigInteger('municipio_id')->nullable()->after('niveis_acesso');
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');
            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropIndex(['municipio_id']);
            $table->dropColumn('municipio_id');
        });
    }
};
