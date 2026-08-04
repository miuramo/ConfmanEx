<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PHP インジェクション・不正 UTF-8 を含むリクエストを前段で遮断する。
 *
 * 対象:
 *  - URL やパラメータキー/値に PHP コードインジェクションパターンを含むリクエスト
 *  - リクエストパラメータのキーに不正な UTF-8 を含むリクエスト
 */
class BlockSuspiciousRequests
{
    /**
     * PHP/シェルインジェクション検出パターン（大文字小文字を区別しない）
     */
    private const INJECTION_PATTERNS = [
        '<?',
        'php_shell_exec(',
        '<?=',
        'phpinfo(',
        'eval(',
        'base64_decode(',
        'system(',
        'exec(',
        'passthru(',
        'shell_exec(',
        'proc_open(',
        'popen(',
        'php://input',
        'php://filter',
        'data://text',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // ① フルURLをチェック
        if ($this->hasInjectionPattern($request->fullUrl())) {
            abort(403);
        }

        // ② リクエストパラメータのキーと値をチェック
        $inputs = $request->all();
        if ($this->arrayHasInjection($inputs)) {
            abort(403);
        }

        // ③ パラメータキーの不正 UTF-8 チェック（値は LogAccess 側でクリーニング済み）
        if ($this->arrayKeysHaveMalformedUtf8($inputs)) {
            abort(400);
        }

        return $next($request);
    }

    /**
     * 文字列にインジェクションパターンが含まれるか判定する
     */
    private function hasInjectionPattern(string $value): bool
    {
        $lower = strtolower($value);
        foreach (self::INJECTION_PATTERNS as $pattern) {
            if (strpos($lower, strtolower($pattern)) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 配列のキー・値を再帰的にインジェクションパターン検査する
     */
    private function arrayHasInjection(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && $this->hasInjectionPattern($key)) {
                return true;
            }
            if (is_string($value) && $this->hasInjectionPattern($value)) {
                return true;
            }
            if (is_array($value) && $this->arrayHasInjection($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 配列キーに不正な UTF-8 が含まれるか再帰的に検査する
     * （値の不正 UTF-8 は LogAccess ミドルウェアで mb_convert_encoding 済み）
     */
    private function arrayKeysHaveMalformedUtf8(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && !mb_check_encoding($key, 'UTF-8')) {
                return true;
            }
            if (is_array($value) && $this->arrayKeysHaveMalformedUtf8($value)) {
                return true;
            }
        }
        return false;
    }
}
