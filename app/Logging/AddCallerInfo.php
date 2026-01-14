<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\LogRecord;

class AddCallerInfo
{
    public function __invoke(Logger $logger): void
    {
        // Illuminate\Log\Logger → Monolog\Logger を取得
        $monolog = $logger->getLogger();

        foreach ($monolog->getHandlers() as $handler) {
            $handler->pushProcessor(function (LogRecord $record) {

                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);

                foreach ($trace as $frame) {
                    if (
                        isset($frame['file']) &&
                        !str_contains($frame['file'], '/vendor/')
                    ) {
                        return $record->with(
                            extra: array_merge($record->extra, [
                                'file' => $frame['file'],
                                'line' => $frame['line'] ?? null,
                            ])
                        );
                    }
                }

                return $record;
            });
        }
    }
}
