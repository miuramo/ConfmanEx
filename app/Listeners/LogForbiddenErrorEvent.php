<?php

namespace App\Listeners;

use App\Events\ForbiddenErrorEvent;
use App\Models\LogForbidden;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogForbiddenErrorEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ForbiddenErrorEvent $event): void
    {
        $rooturl = $event->request->root();
        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: null ;
        // データベースにログを保存する処理
        $uid = (Auth::id() != null) ? Auth::id() : -1;
        LogForbidden::create([
            'uid' => $uid,
            'url' => substr($event->request->fullUrl(), strlen($rooturl)),
            'method' => $event->request->method(),
            'mes' => $event->exception->getMessage()." ". $remote_addr ,
            'request' => $event->request->all(),
        ]);
    }
}
