<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Models\Regist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegistController extends Controller
{
    public function show()
    {
        return redirect()->route('regist.index');
    }
    //
    public function index()
    {
        // 参加登録の一覧を表示する
        return view('regist.index');
    }
    /**
     * 参加登録を開始する
     */
    public function create()
    {
        $canRegist = false;
        if (auth()->user()->can('role_any', 'pc|reviewer|metareviewer')) {
            $canRegist = true;
        }
        if (auth()->user()->can('has_accepted_papers')){
            $canRegist = true;
        }
        if (auth()->user()->can('has_submitted_papers')){
            $canRegist = true;
        }
        if (!$canRegist) {
            return redirect()->route('regist.index')->with('feedback.error', '参加登録の権限がありません。（現在は採録著者とプログラム委員のみ登録できます。）');
        }

        $reg = Regist::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        if ($reg) {
            // 参加登録が既に存在する場合は、編集画面にリダイレクト
            return redirect()->route('regist.edit', ['regist' => $reg->id]);
        }
        // 参加登録のフォームを表示する
        return back()->with('feedback.error', '参加登録エラー');
    }

    public function entry()
    {
        // 参加登録のエントリーフォームを表示する
        return view('regist.entry');
    }

    public function edit($id)
    {
        if (!is_numeric($id)){
            return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録IDです。');
        }
        // 参加登録の編集フォームを表示する
        $reg = Regist::findOrFail($id);
        if ($reg->user_id !== auth()->id()) {
            return redirect()->route('regist.index')->with('feedback.error', '他のユーザーの参加登録を編集することはできません。');
        }
        return view('regist.edit', ['regist' => $reg])->with('regid', $id)->with('reg', $reg);
    }
    public function email($id)
    {
        if (!is_numeric($id)){
            return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録IDです。');
        }
        // 参加登録の編集フォームを表示する
        $reg = Regist::findOrFail($id);
        if ($reg->user_id !== auth()->id()) {
            return redirect()->route('regist.index')->with('feedback.error', '他のユーザーの参加登録を編集することはできません。');
        }
        if ($reg->valid) {
            // メール送信処理を実行
            Mail::to($reg->user)->send(new RegistrationConfirmation($reg));
            return redirect()->route('regist.index')->with('feedback.success', '参加登録内容をメールで送信しました。');
        } else {
            return redirect()->route('regist.edit', ['regist' => $id])->with('feedback.error', '参加登録内容が無効です。');
        }
    }

    public function destroy($id)
    {
        // 参加登録を削除する
        $reg = Regist::findOrFail($id);
        $reg->delete();
        return redirect()->route('regist.index')->with('feedback.success', '参加登録を削除しました。');
    }
}
