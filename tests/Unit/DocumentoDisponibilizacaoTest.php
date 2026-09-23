<?php

namespace Tests\Unit;

use App\Models\DocumentoAssinatura;
use App\Models\DocumentoDigital;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class DocumentoDisponibilizacaoTest extends TestCase
{
    private function documento(?string $finalizado = null, ?string $assinado = null): DocumentoDigital
    {
        $documento = new DocumentoDigital();
        $documento->setDateFormat('Y-m-d H:i:s');
        $documento->forceFill([
            'status' => 'assinado',
            'created_at' => '2026-09-16 09:00:00',
            'finalizado_em' => $finalizado,
        ]);
        $assinatura = new DocumentoAssinatura();
        $assinatura->setDateFormat('Y-m-d H:i:s');
        $assinatura->forceFill([
            'obrigatoria' => true,
            'status' => 'assinado',
            'assinado_em' => $assinado,
        ]);
        $documento->setRelation('assinaturas', new Collection([$assinatura]));

        return $documento;
    }

    public function test_exibe_liberacao_e_nao_criacao_do_rascunho(): void
    {
        $documento = $this->documento('2026-09-18 14:43:00', '2026-09-18 14:42:00');
        $this->assertSame('2026-09-18 14:43:00', $documento->data_disponibilizacao->format('Y-m-d H:i:s'));
    }

    public function test_liberacao_nunca_antecede_assinatura_obrigatoria(): void
    {
        $documento = $this->documento('2026-09-16 10:00:00', '2026-09-18 14:43:00');
        $this->assertSame('2026-09-18 14:43:00', $documento->data_disponibilizacao->format('Y-m-d H:i:s'));
    }

    public function test_legado_sem_finalizacao_utiliza_assinatura(): void
    {
        $documento = $this->documento(null, '2026-09-18 14:43:00');
        $this->assertSame('2026-09-18 14:43:00', $documento->data_disponibilizacao->format('Y-m-d H:i:s'));
    }

    public function test_nao_inventa_data_para_documento_sem_registro_de_liberacao(): void
    {
        $this->assertNull($this->documento()->data_disponibilizacao);
    }

    public function test_pendente_nao_tem_data_de_disponibilizacao(): void
    {
        $documento = $this->documento('2026-09-18 14:43:00');
        $documento->assinaturas->first()->status = 'pendente';
        $this->assertNull($documento->data_disponibilizacao);
        $documento->status = 'rascunho';
        $this->assertNull($documento->data_disponibilizacao);
    }

    public function test_documento_fisico_utiliza_cadastro_quando_nao_tem_finalizacao(): void
    {
        $documento = $this->documento();
        $documento->tipo_origem = 'fisico';
        $this->assertSame('2026-09-16 09:00:00', $documento->data_disponibilizacao->format('Y-m-d H:i:s'));
    }
}
