<?php

namespace Database\Seeders;

use App\Models\TipoProcesso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoProcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nome' => 'Licenciamento',
                'codigo' => 'licenciamento',
                'descricao' => 'Processo de licenciamento sanitário anual do estabelecimento',
                'anual' => true,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 1,
            ],
            [
                'nome' => 'Análise de Rotulagem',
                'codigo' => 'analise_rotulagem',
                'descricao' => 'Análise e aprovação de rótulos de produtos',
                'anual' => false,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 2,
            ],
            [
                'nome' => 'Projeto Arquitetônico',
                'codigo' => 'projeto_arquitetonico',
                'descricao' => 'Análise de projeto arquitetônico para adequação sanitária',
                'anual' => false,
                'usuario_externo_pode_abrir' => true,
                'ativo' => true,
                'ordem' => 3,
            ],
            [
                'nome' => 'Administrativo',
                'codigo' => 'administrativo',
                'descricao' => 'Processos administrativos diversos',
                'anual' => false,
                'usuario_externo_pode_abrir' => false,
                'ativo' => true,
                'ordem' => 4,
            ],
            [
                'nome' => 'Descentralização',
                'codigo' => 'descentralizacao',
                'descricao' => 'Processos de descentralização de ações de vigilância sanitária',
                'anual' => false,
                'usuario_externo_pode_abrir' => false,
                'ativo' => true,
                'ordem' => 5,
            ],
            [
                'nome' => 'Credenciamento de Unidade Móvel',
                'codigo' => 'credenciamento_movel',
                'descricao' => 'Credenciamento de estabelecimentos de outros estados que prestam serviço itinerante/temporário no Tocantins com unidades móveis',
                'anual' => false,
                // Abertura automática ocorre na aprovação do cadastro (etapa futura),
                // por isso o usuário externo não abre este processo manualmente.
                'usuario_externo_pode_abrir' => false,
                'usuario_externo_pode_visualizar' => true,
                // A competência é resolvida dinamicamente por município pela pactuação,
                // seguindo o mesmo comportamento dos demais tipos.
                'competencia' => 'estadual',
                'ativo' => true,
                'ordem' => 6,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoProcesso::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
        
        $this->command->info('Tipos de processos cadastrados com sucesso!');
    }
}
