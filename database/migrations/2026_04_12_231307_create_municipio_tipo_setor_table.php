<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar tabela pivot
        Schema::create('municipio_tipo_setor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_setor_id');
            $table->unsignedBigInteger('municipio_id');
            $table->timestamps();

            $table->foreign('tipo_setor_id')->references('id')->on('tipo_setores')->onDelete('cascade');
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');
            $table->unique(['tipo_setor_id', 'municipio_id']);
        });

        // Migrar dados existentes do municipio_id para a pivot
        $setoresComMunicipio = DB::table('tipo_setores')->whereNotNull('municipio_id')->get();
        foreach ($setoresComMunicipio as $setor) {
            DB::table('municipio_tipo_setor')->insert([
                'tipo_setor_id' => $setor->id,
                'municipio_id' => $setor->municipio_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Remover coluna antiga
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropIndex(['municipio_id']);
            $table->dropColumn('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');
            $table->index('municipio_id');
        });

        // Restaurar primeiro municipio da pivot
        $pivots = DB::table('municipio_tipo_setor')
            ->select('tipo_setor_id', DB::raw('MIN(municipio_id) as municipio_id'))
            ->groupBy('tipo_setor_id')
            ->get();
        foreach ($pivots as $pivot) {
            DB::table('tipo_setores')->where('id', $pivot->tipo_setor_id)->update(['municipio_id' => $pivot->municipio_id]);
        }

        Schema::dropIfExists('municipio_tipo_setor');
    }
};
