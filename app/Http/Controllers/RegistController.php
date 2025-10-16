<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Models\Regist;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegistController extends Controller
{
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
        $allowed_users = [];
        $canRegist = false;
        if (Setting::getval('REG_START_FOR_PCACC')) {
            if (auth()->user()->can('role_any', 'pc|acc')) {
                $canRegist = true;
                $allowed_users[] = "PC長・会計担当";
            }
        }
        if (Setting::getval('REG_START_FOR_REVIEWERS')) {
            if (auth()->user()->can('role_any', 'reviewer|metareviewer')) {
                $canRegist = true;
                $allowed_users[] = "査読者";
            }
        }
        if (Setting::getval('REG_START_FOR_ACCEPTED_AUTHORS')) {
            if (auth()->user()->can('has_accepted_papers')) {
                $canRegist = true;
                $allowed_users[] = "採録著者";
            }
        }
        if (Setting::getval('REG_START_FOR_VALID_AUTHORS')) {
            if (auth()->user()->can('has_submitted_papers')) {
                $canRegist = true;
                $allowed_users[] = "投稿完了済み著者";
            }
        }
        if (Setting::getval('REG_START_FOR_ALL')) {
            $canRegist = true;
            $allowed_users[] = "アカウント保持者全員";
        }

        if (!$canRegist) {
            if (count($allowed_users) == 0) {
                return redirect()->route('regist.index')->with('feedback.error', '参加登録の権限がありません。（現在は誰も登録できません。）');
            } else {
                $alu = implode('、', $allowed_users);
                return redirect()->route('regist.index')->with('feedback.error', '参加登録の権限がありません。（現在は' . $alu . 'が登録できます。）');
            }
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

    public function create_for_sponsors($token = null)
    {
        if ($token !== Regist::sponsortoken()) {
            return redirect()->route('regist.index')->with('feedback.error', '不正なスポンサー参加登録トークンです。');
        }
        $reg = Regist::firstOrCreate([
            'user_id' => auth()->id(),
        ]);
        if ($reg) {
            // 参加登録が既に存在する場合は、編集画面にリダイレクト
            return redirect()->route('regist.edit', ['regist' => $reg->id, 'token' => $reg->token()]);
        }
        // 参加登録のフォームを表示する
        return redirect()->route('regist.index')->with('feedback.error', '参加登録エラー');
    }
    public function create_for_admin($user_id)
    {
        if (!auth()->user()->can('role_any', 'pc|acc')) {
            return redirect()->route('regist.index')->with('feedback.error', '参加登録の代理入力の権限がありません。');
        }
        if (!is_numeric($user_id)) {
            return redirect()->route('regist.index')->with('feedback.error', '不正なユーザIDです。');
        }
        $reg = Regist::firstOrCreate([
            'user_id' => $user_id,
        ]);
        if ($reg) {
            // 参加登録が既に存在する場合は、編集画面にリダイレクト
            return redirect()->route('regist.edit', ['regist' => $reg->id, 'token' => $reg->token()]);
        }
        // 参加登録のフォームを表示する
        return redirect()->route('regist.index')->with('feedback.error', '参加登録エラー');
    }
    public static function allowed_users_string()
    {
        $allowed_users = [];
        if (Setting::getval('REG_START_FOR_PCACC')) {
            $allowed_users[] = "PC長・会計担当";
        }
        if (Setting::getval('REG_START_FOR_REVIEWERS')) {
            $allowed_users[] = "査読者";
        }
        if (Setting::getval('REG_START_FOR_ACCEPTED_AUTHORS')) {
            $allowed_users[] = "採録著者";
        }
        if (Setting::getval('REG_START_FOR_VALID_AUTHORS')) {
            $allowed_users[] = "投稿完了済み著者";
        }
        if (Setting::getval('REG_START_FOR_ALL')) {
            $allowed_users[] = "アカウント保持者全員";
        }
        if (count($allowed_users) == 0) {
            return "現在は誰も登録できません。会計担当者またはPC長に設定を依頼してください。";
        } else {
            $alu = implode('、', $allowed_users);
            return "現在は" . $alu . "が登録できます。";
        }
        return $res;
    }

    public function entry()
    {
        // 参加登録のエントリーフォームを表示する
        return view('regist.entry');
    }

    public function edit($id, $token = null)
    {
        if (!is_numeric($id)) {
            return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録IDです。');
        }
        // 参加登録の編集フォームを表示する
        $reg = Regist::findOrFail($id);
        $with_token = false;
        // tokenが指定されている場合、tokenを確認する。ログインユーザなら誰でもいつでも編集可能。
        if ($token !== null) {
            if ($reg->token() !== $token) {
                return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録編集トークンです。');
            }
            $with_token = true;
        } else if ($reg->user_id !== auth()->id()) {
            return redirect()->route('regist.index')->with('feedback.error', '他のユーザーの参加登録を編集することはできません。');
        }

        if ($with_token || auth()->user()->can('is_now_early')) {
            $reg->valid = false; // 編集開始時点で無効にする
            $reg->save();
            return view('regist.edit', ['regist' => $reg])->with('regid', $id)->with('reg', $reg);
        } else {
            return redirect()->route('regist.index')->with('feedback.error', '現在は参加登録の編集はできません。');
        }
    }
    public function show($id, $token = null)
    {
        if (!is_numeric($id)) {
            return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録IDです。');
        }
        // 参加登録の確認画面フォームを表示する
        $reg = Regist::findOrFail($id);
        $with_token = false;
        // tokenが指定されている場合、tokenを確認する。ログインユーザなら誰でもいつでも編集可能。
        if ($token !== null) {
            if ($reg->token() !== $token) {
                return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録参照トークンです。');
            }
            $with_token = true;
        } else if ($reg->user_id !== auth()->id()) {
            return redirect()->route('regist.index')->with('feedback.error', '他のユーザーの参加登録を参照することはできません。');
        }
        return view('regist.show', ['regist' => $reg])->with('regid', $id)->with('reg', $reg);
    }

    public function email($id)
    {
        if (!is_numeric($id)) {
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

    /**
     * 関連アンケートも含めて、参加登録を削除する
     */
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            return redirect()->route('regist.index')->with('feedback.error', '不正な参加登録IDです。');
        }
        $pcacc = auth()->user()->can('role_any', 'pc|acc');
        $is_owner = false;
        $is_early = auth()->user()->can('is_now_early');
        $reg = Regist::find($id);
        if ($reg && $reg->user_id === auth()->id()) {
            $is_owner = true;
        }
        if (!$pcacc && !$is_owner) {
            return redirect()->route('regist.index')->with('feedback.error', '他のユーザの参加登録を削除することはできません。');
        }
        // 管理者(pcacc)はいつでも削除可能
        // ユーザ本人は、現在が早期登録期間中であれば削除可能
        if (!$pcacc && !$is_early) {
            return redirect()->route('regist.index')->with('feedback.error', '現在は参加登録の削除はできません。');
        }

        // 参加登録を削除する
        $reg->delete();

        // アンケート実体も削除
        // 対象アンケートは、Enquete.withpaper = false 
        $enqIDs = \App\Models\Enquete::where('withpaper', false)->pluck('id')->toArray();
        \App\Models\EnqueteAnswer::where('user_id', $reg->user_id)
            ->whereIn('enquete_id', $enqIDs)
            ->delete();
        return back()->with('feedback.success', '参加登録を削除しました。');
    }
}
