<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_interno_convites', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('token', 100)->unique();
            $table->string('nivel_acesso', 50);
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->nullOnDelete();
            $table->foreignId('criado_por')->constrained('usuarios_internos')->cascadeOnDelete();
            $table->timestamp('expira_em')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->timestamps();

            $table->index(['nivel_acesso', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_interno_convites');
    }
};
