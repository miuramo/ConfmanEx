<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\FilterHandler;
use Monolog\LogRecord;

class SkipOn451Tap
{
    public function __invoke(Logger $logger): void
    {
        // 既存ハンドラを走査して SlackWebhookHandler を探す
        $monolog = $logger->getLogger();
        $handlers = $monolog->getHandlers();
        $newHandlers = [];

        foreach ($handlers as $handler) {
            if ($this->isSlackHandler($handler)) {
                // FilterHandler でラップして条件付きでスキップ
                $filteredHandler = new Custom451FilterHandler($handler);
                $newHandlers[] = $filteredHandler;
            } else {
                $newHandlers[] = $handler;
            }
        }

        // 全てのハンドラを置き換え
        $monolog->setHandlers($newHandlers);
    }

    /**
     * ログを送信するべきかどうかを判定
     */
    public static function shouldLog($record): bool
    {
        // LogRecord オブジェクトか配列かを判定
        if ($record instanceof LogRecord) {
            $message = $record->message;
            $context = $record->context;
        } else {
            $message = $record['message'] ?? '';
            $context = $record['context'] ?? [];
        }

        // ① メッセージに "451" または "4.4.2" が含まれる場合はスキップ
        if (stripos($message, '451') !== false && stripos($message, '4.4.2') !== false) {
            return false; // ログを送信しない
        }
        if (stripos($message, '550') !== false && stripos($message, 'Invalid') !== false) {
            return false; // ログを送信しない 550 Invalid recipient
        }
        if (stripos($message, 'Unable to write bytes') !== false) {
            return false; // ログを送信しない 
        }
        if (stripos($message, 'has been closed unexpectedly') !== false) {
            return false; // ログを送信しない 
        }
        if (stripos($message, 'but got empty code') !== false) {
            return false; // ログを送信しない 
        }

        // ② context に exception がある場合はその中身をチェック
        // if (!empty($context['exception']) && is_object($context['exception'])) {
        //     $exception = $context['exception'];
            
        //     // 例外のコードが 451 の場合はスキップ
        //     if (method_exists($exception, 'getCode') && (int) $exception->getCode() === 451) {
        //         return false;
        //     }

        //     // 例外メッセージに 451 または 4.4.2 が含まれる場合はスキップ
        //     if (method_exists($exception, 'getMessage')) {
        //         $exceptionMessage = $exception->getMessage();
        //         if (stripos($exceptionMessage, '451') !== false || stripos($exceptionMessage, '4.4.2') !== false) {
        //             return false;
        //         }
        //     }
        // }

        // ③ context に 'previous' exception がある場合もチェック
        // if (!empty($context['exception']) && is_object($context['exception']) && method_exists($context['exception'], 'getPrevious')) {
        //     $previous = $context['exception']->getPrevious();
        //     if ($previous && method_exists($previous, 'getMessage')) {
        //         $prevMessage = $previous->getMessage();
        //         if (stripos($prevMessage, '451') !== false || stripos($prevMessage, '4.4.2') !== false) {
        //             return false;
        //         }
        //     }
        // }

        // それ以外は送信する
        return true;
    }

    protected function isSlackHandler($handler): bool
    {
        // Slack handler の型に合わせて判定を増やす
        // 例: Monolog\Handler\SlackWebhookHandler, \Monolog\Handler\SlackHandler など
        return $handler instanceof SlackWebhookHandler
            || $handler instanceof \Monolog\Handler\SlackHandler
            || $handler instanceof \Monolog\Handler\SlackbotHandler; // 必要に応じて
    }
}

/**
 * HTTP 451エラーをフィルタリングするカスタムハンドラ
 */
class Custom451FilterHandler implements HandlerInterface
{
    private HandlerInterface $handler;
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function isHandling(LogRecord $record): bool
    {
        return $this->handler->isHandling($record);
    }

    public function handle(LogRecord $record): bool
    {
        if (!SkipOn451Tap::shouldLog($record)) {
            return false;
        }

        return $this->handler->handle($record);
    }

    public function handleBatch(array $records): void
    {
        $filteredRecords = array_filter($records, [SkipOn451Tap::class, 'shouldLog']);
        $this->handler->handleBatch($filteredRecords);
    }

    public function close(): void
    {
        $this->handler->close();
    }
}