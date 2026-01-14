<?php

namespace App\Logging;

use App\Models\ErrorLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseLogHandler extends AbstractProcessingHandler
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
        } catch (\Throwable $e) {
            // 無限ループ防止
        }
    }
}