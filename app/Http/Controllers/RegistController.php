<?php

namespace App\Http\Controllers;

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
        // 参加登録のフォームを表示する
        return view('regist.create');
    }

    public function entry()
    {
        // 参加登録のエントリーフォームを表示する
        return view('regist.entry');
    }
}
