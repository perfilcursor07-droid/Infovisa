<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identifica estabelecimentos de Pessoa Física cadastrados como Produtor Rural.
     * Campo apenas informativo: não altera a lógica de competência/pactuação.
     */
    public function up(): void
    {
        if (Schema::hasColumn('estabelecimentos', 'produtor_rural')) {
            return;
        }

        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->boolean('produtor_rural')->default(false)->after('tipo_pessoa')
                ->comment('Indica se o cadastro de Pessoa Física é de um Produtor Rural');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('estabelecimentos', 'produtor_rural')) {
            return;
        }

        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('produtor_rural');
        });
    }
};
