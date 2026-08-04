<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detecta POST/PUT esvaziados pelo PHP quando o body ultrapassa post_max_size.
 * Sem isso o Laravel costuma responder 405 (some o _method=PUT).
 */
class CheckPostSize
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->getRealMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($contentLength <= 0) {
            return $next($request);
        }

        $postMax = $this->parseIniSize((string) ini_get('post_max_size'));
        $bodyVazio = count($request->request->all()) === 0
            && count($request->allFiles()) === 0
            && empty($request->getContent());

        if ($contentLength > $postMax && $bodyVazio) {
            $limite = ini_get('post_max_size');
            $mensagem = "O conteúdo enviado é maior que o limite do servidor (post_max_size={$limite}). "
                . 'Reduza o tamanho das imagens ou peça ao administrador para aumentar o limite.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $mensagem, 'error' => $mensagem], 413);
            }

            return redirect()
                ->back()
                ->with('error', $mensagem);
        }

        return $next($request);
    }

    private function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
