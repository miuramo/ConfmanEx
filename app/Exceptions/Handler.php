<?php

namespace App\Exceptions;

use App\Events\ForbiddenErrorEvent;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // セッションタイムアウト時はログインページにリダイレクトさせる
    // https://qiita.com/miki_grapes/items/8d8104cf3cba614ffac8
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            return redirect()->route('login');
        }
        if ($exception instanceof HttpException) {
            event(new ForbiddenErrorEvent($exception, $request));
        }
        return parent::render($request, $exception);
    }

    // public function report(Throwable $exception)
    // {
    //     if ($exception instanceof UnexpectedResponseException) {
    //         // 通知を送信
    //         Notification::route('mail', 'motoki.miura@p.chibakoudai.jp')
    //             ->notify(new MailFailureNotification($exception->getMessage()));
    //     }

    //     parent::report($exception);
    // }
}
