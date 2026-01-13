<?php

namespace App\Logging;

use App\Models\ErrorLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class DatabaseLogger extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        try {
        ErrorLog::create([
            'level'     => $record->level->name,
            'message'   => $record->message,
            'context'   => $record->context,
            'extra'     => $record->extra,
            'logged_at' => $record->datetime,
        ]);
        } catch (\Exception $e) {
            // // ログの保存に失敗した場合の処理（例: ファイルログにフォールバック）
            // error_log('Failed to write log to database: ' . $e->getMessage());
        }
    }
}
