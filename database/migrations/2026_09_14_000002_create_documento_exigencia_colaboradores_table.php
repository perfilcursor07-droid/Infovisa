<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_exigencia_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->cascadeOnDelete();
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->cascadeOnDelete();
            $table->foreignId('atribuido_por')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->string('area', 255)->nullable();
            $table->date('prazo_interno')->nullable();
            $table->enum('status', ['pendente', 'concluido'])->default('pendente');
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->index(['usuario_interno_id', 'status']);
            $table->index(['documento_digital_id', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_exigencia_colaboradores');
    }
};
