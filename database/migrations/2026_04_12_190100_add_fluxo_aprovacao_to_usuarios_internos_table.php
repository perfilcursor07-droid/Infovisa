<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios_internos', function (Blueprint $table) {
            $table->string('status_cadastro', 20)
                ->default('aprovado')
                ->after('ativo');
            $table->foreignId('convite_id')
                ->nullable()
                ->after('municipio_id')
                ->constrained('usuario_interno_convites')
                ->nullOnDelete();
            $table->foreignId('aprovado_por')
                ->nullable()
                ->after('convite_id')
                ->constrained('usuarios_internos')
                ->nullOnDelete();
            $table->timestamp('aprovado_em')
                ->nullable()
                ->after('aprovado_por');
            $table->text('observacao_aprovacao')
                ->nullable()
                ->after('aprovado_em');

            $table->index('status_cadastro');
            $table->index('convite_id');
            $table->index('aprovado_por');
        });

        DB::table('usuarios_internos')
            ->whereNull('aprovado_em')
            ->update([
                'status_cadastro' => 'aprovado',
                'aprovado_em' => DB::raw('created_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_internos', function (Blueprint $table) {
            $table->dropForeign(['convite_id']);
            $table->dropForeign(['aprovado_por']);
            $table->dropIndex(['status_cadastro']);
            $table->dropIndex(['convite_id']);
            $table->dropIndex(['aprovado_por']);
            $table->dropColumn([
                'status_cadastro',
                'convite_id',
                'aprovado_por',
                'aprovado_em',
                'observacao_aprovacao',
            ]);
        });
    }
};
