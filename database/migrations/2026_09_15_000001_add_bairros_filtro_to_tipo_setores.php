<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->jsonb('bairros_filtro')->nullable()->after('ceps_filtro');
        });

        DB::table('tipo_setores')
            ->where(function ($query) {
                $query->whereRaw('lower(nome) like ?', ['%luzimangues%'])
                    ->orWhereRaw('lower(codigo) like ?', ['%luzimangues%']);
            })
            ->whereNull('bairros_filtro')
            ->update([
                'bairros_filtro' => json_encode(['LUZIMANGUES', 'JARDIM MILAO']),
            ]);
    }

    public function down(): void
    {
        Schema::table('tipo_setores', function (Blueprint $table) {
            $table->dropColumn('bairros_filtro');
        });
    }
};
