<?php
/**
 * Script de diagnóstico para imagens no PDF.
 * Execute: php diagnostico_imagem.php
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNÓSTICO DE IMAGENS NO PDF ===\n\n";

// 1. Configurações
echo "1. CONFIGURAÇÕES:\n";
echo "   APP_URL: " . config('app.url') . "\n";
echo "   storage_path: " . storage_path('app/public') . "\n";
echo "   public_path: " . public_path() . "\n";
echo "   public/storage existe: " . (file_exists(public_path('storage')) ? 'SIM' : 'NÃO') . "\n";
echo "   public/storage é symlink: " . (is_link(public_path('storage')) ? 'SIM' : 'NÃO') . "\n";
if (is_link(public_path('storage'))) {
    echo "   symlink aponta para: " . readlink(public_path('storage')) . "\n";
}
echo "   allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'SIM' : 'NÃO') . "\n";
echo "   cURL disponível: " . (function_exists('curl_init') ? 'SIM' : 'NÃO') . "\n";
echo "\n";

// 2. Busca documento recente com imagem
echo "2. BUSCANDO DOCUMENTO COM IMAGEM (processo 776):\n";
$documento = \App\Models\DocumentoDigital::where('processo_id', 776)
    ->latest()
    ->first();

if (!$documento) {
    // Tenta buscar qualquer documento recente com img
    $documento = \App\Models\DocumentoDigital::where('conteudo', 'like', '%<img%')
        ->latest()
        ->first();
}

if (!$documento) {
    echo "   NENHUM DOCUMENTO COM IMAGEM ENCONTRADO!\n";
    exit(1);
}

echo "   Documento encontrado: ID={$documento->id}, Numero={$documento->numero_documento}\n";
echo "   Status: {$documento->status}\n";
echo "   Tem arquivo_pdf: " . ($documento->arquivo_pdf ? $documento->arquivo_pdf : 'NÃO') . "\n";

// 3. Analisa o conteúdo HTML
echo "\n3. ANALISANDO CONTEÚDO HTML:\n";
$conteudo = $documento->conteudo ?? '';
echo "   Tamanho do conteúdo: " . strlen($conteudo) . " bytes\n";

// Encontra todas as tags img
preg_match_all('/src=(["\'])(.*?)\1/i', $conteudo, $matches);
$urls = $matches[2] ?? [];

echo "   Quantidade de src= encontrados: " . count($urls) . "\n\n";

foreach ($urls as $i => $url) {
    echo "   --- Imagem #" . ($i + 1) . " ---\n";
    if (str_starts_with($url, 'data:')) {
        echo "   Tipo: data-URI (base64 inline)\n";
        echo "   Primeiros 80 chars: " . substr($url, 0, 80) . "...\n";
        echo "   RESULTADO: OK - DomPDF suporta data-URI\n";
    } else {
        echo "   URL: {$url}\n";
        
        // Tenta resolver localmente
        $reflection = new ReflectionMethod(\App\Models\DocumentoDigital::class, 'resolverCaminhoLocalImagem');
        $reflection->setAccessible(true);
        $caminhoLocal = $reflection->invoke(null, $url);
        
        echo "   Caminho local resolvido: " . ($caminhoLocal ?? 'NULL') . "\n";
        if ($caminhoLocal) {
            echo "   Arquivo existe: " . (is_file($caminhoLocal) ? 'SIM' : 'NÃO') . "\n";
            if (is_file($caminhoLocal)) {
                echo "   Tamanho: " . filesize($caminhoLocal) . " bytes\n";
                echo "   MIME: " . mime_content_type($caminhoLocal) . "\n";
            }
        }
        
        // Tenta /storage/ diretamente
        if (str_contains($url, '/storage/')) {
            $posStorage = strrpos($url, '/storage/');
            $relativo = substr($url, $posStorage + 9);
            $caminhoStorage = storage_path('app/public/' . $relativo);
            echo "   Caminho storage direto: {$caminhoStorage}\n";
            echo "   Existe em storage: " . (is_file($caminhoStorage) ? 'SIM' : 'NÃO') . "\n";
            
            $caminhoPublic = public_path('storage/' . $relativo);
            echo "   Caminho public/storage: {$caminhoPublic}\n";
            echo "   Existe em public: " . (is_file($caminhoPublic) ? 'SIM' : 'NÃO') . "\n";
        }
        
        // Tenta via HTTP
        echo "   Tentando cURL... ";
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true, // HEAD request apenas
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $erro = curl_error($ch);
            curl_close($ch);
            echo "HTTP {$httpCode}" . ($erro ? " (erro: {$erro})" : "") . "\n";
        } else {
            echo "cURL não disponível\n";
        }
    }
    echo "\n";
}

// 4. Testa conteudoParaPdf
echo "4. TESTANDO conteudoParaPdf():\n";
$resultado = $documento->conteudoParaPdf();
echo "   Tamanho resultado: " . strlen($resultado) . " bytes\n";

preg_match_all('/src=(["\'])(.*?)\1/i', $resultado, $matchesResultado);
$urlsResultado = $matchesResultado[2] ?? [];
$dataUris = 0;
$httpUrls = 0;
foreach ($urlsResultado as $u) {
    if (str_starts_with($u, 'data:')) $dataUris++;
    else $httpUrls++;
}
echo "   Imagens data-URI: {$dataUris}\n";
echo "   Imagens HTTP (não convertidas): {$httpUrls}\n";

if ($httpUrls > 0) {
    echo "\n   ⚠️  IMAGENS NÃO CONVERTIDAS:\n";
    foreach ($urlsResultado as $u) {
        if (!str_starts_with($u, 'data:')) {
            echo "   - {$u}\n";
        }
    }
}

// 5. Verifica logs recentes
echo "\n5. LOGS RECENTES (últimas 30 linhas com 'embutir' ou 'baixar'):\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -200);
    $count = 0;
    foreach ($lastLines as $line) {
        if (str_contains($line, 'embutirImagens') || str_contains($line, 'baixarImagem') || str_contains($line, 'imagem não resolvida')) {
            echo "   " . trim($line) . "\n";
            $count++;
            if ($count >= 10) break;
        }
    }
    if ($count === 0) echo "   Nenhum log relevante encontrado\n";
} else {
    echo "   Arquivo de log não encontrado\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
