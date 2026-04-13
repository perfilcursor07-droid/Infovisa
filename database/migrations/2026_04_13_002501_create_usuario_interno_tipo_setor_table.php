<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_interno_tipo_setor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_interno_id');
            $table->unsignedBigInteger('tipo_setor_id');
            $table->timestamps();

            $table->foreign('usuario_interno_id')->references('id')->on('usuarios_internos')->onDelete('cascade');
            $table->foreign('tipo_setor_id')->references('id')->on('tipo_setores')->onDelete('cascade');
            $table->unique(['usuario_interno_id', 'tipo_setor_id'], 'ui_ts_unique');
        });

        // Migrar dados existentes: campo setor (codigo) -> pivot
        $usuarios = DB::table('usuarios_internos')->whereNotNull('setor')->where('setor', '!=', '')->get();
        foreach ($usuarios as $usuario) {
            $tipoSetor = DB::table('tipo_setores')->where('codigo', $usuario->setor)->first();
            if ($tipoSetor) {
                DB::table('usuario_interno_tipo_setor')->insertOrIgnore([
                    'usuario_interno_id' => $usuario->id,
                    'tipo_setor_id' => $tipoSetor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_interno_tipo_setor');
    }
};
