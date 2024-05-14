<?php

namespace App\Http\Requests;

use App\Mail\FirstInvitation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class UserEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    /**
     * エントリー時の処理
     */
    public function shori(): object
    {
        $em = $this->input("email");
        try {
            $user = User::create([
                'name' => User::$initialName,
                'email' => $em,
                'password' => Hash::make($em),
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[0] == 23000) {
                return redirect()->route('entry')->with('feedback.error', "すでに登録済みです。 ({$em})");
            } else {
                dd($e);
            }
        }
        // $user->sendEmailVerificationNotification();
        // $token = $user->getRememberToken();

        // 初回のみ、パスワード再設定メールを変更している。see User.php
        Password::sendResetLink($this->only('email'));
        return redirect()->route('entry')->with('feedback.success', "メールで認証URLを送信しました ({$em})");
        // Mail::to($em)->send(new FirstInvitation($em));
    }
}
