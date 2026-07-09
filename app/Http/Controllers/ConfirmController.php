<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfirmController extends Controller
{

    // 編集画面
    public function edit(Request $request, int $grp = 0)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        $titles = [
            0 => 'トップページ',
            1 => '新規投稿',
            2 => '連絡用メールアドレス',
            9 => '参加登録',
        ];
        $title = $titles[$grp] ?? 'その他';
        $tableName = 'confirms';
        $coldetails = AdminController::column_details($tableName);
        foreach ($coldetails as $field => $type) {
            if ($field == 'id' || $field == 'created_at' || $field == 'updated_at') {
                unset($coldetails[$field]);
            }
        }
        $coldetails['COPY'] = 'COPY';
        $note = null;
        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy = [];
        $tableComments = AdminController::get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->where('grp', $grp)->orderBy('name')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->where('grp', $grp)->count();
        return view('confirm.edit')->with(compact("tableName", "coldetails", "data", "whereBy", "numdata", "tableComments", "title", "titles", "note", "grp"));
    }
    // nameを昇順に並べ替え、連番を振り直す
    public function renumber_name(int $grp)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $tableName = 'confirms';
        $data = DB::table($tableName)->where('grp', $grp)->orderBy('name')->get();
        // nameのうち、数字を除く部分文字列を取得する
        $prefixes = $data->map(function ($row) {
            return preg_replace('/\d+/', '', $row->name);
        })->unique();

        $i = 1;
        foreach ($data as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['name' => $prefixes[0] . sprintf('%02d', $i)]);
            $i++;
        }
        return redirect()->route('confirm.edit', ['grp' => $grp]);
    }
    public function edit_copy(Request $request, int $copy_id, int $grp)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        $tableName = 'confirms';
        $data = DB::table($tableName)->where('id', $copy_id)->first();
        if (!$data) abort(404);
        $newData = (array)$data;
        unset($newData['id']);
        $newData['name'] .= '_copy';
        DB::table($tableName)->insert($newData);
        return redirect()->route('confirm.edit', ['grp' => $grp]);
    }
    public function edit_delete(Request $request, int $del_id, int $grp)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $tableName = 'confirms';
        DB::table($tableName)->where('id', $del_id)->delete();
        return redirect()->route('confirm.edit', ['grp' => $grp]);
    }
}
