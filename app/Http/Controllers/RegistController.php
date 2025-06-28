<?php

namespace App\Http\Controllers;

use App\Models\Regist;
use Illuminate\Http\Request;

class RegistController extends Controller
{
    //
    public function index()
    {
        // 参加登録の一覧を表示する
        return view('regist.index');
    }
    public function create()
    {
        $reg = Regist::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        if ($reg) {
            // 参加登録が既に存在する場合は、編集画面にリダイレクト
            return redirect()->route('regist.edit', ['regist' => $reg->id]);
        }
        // 参加登録のフォームを表示する
        return back()->with('error', '参加登録エラー');
    }

    public function entry()
    {
        // 参加登録のエントリーフォームを表示する
        return view('regist.entry');
    }

    public function edit($id)
    {
        if (!is_numeric($id)){
            return redirect()->route('regist.index')->with('error', '不正な参加登録IDです。');
        }
        // 参加登録の編集フォームを表示する
        $reg = Regist::findOrFail($id);
        return view('regist.edit', ['regist' => $reg])->with('regid', $id)->with('reg', $reg);
    }

    public function destroy($id)
    {
        // 参加登録を削除する
        $reg = Regist::findOrFail($id);
        $reg->delete();
        return redirect()->route('regist.index')->with('success', '参加登録を削除しました。');
    }
}
