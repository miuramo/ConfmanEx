<?php

namespace App\Http\Controllers;

use App\Exports\ViewpointsExport;
use App\Http\Requests\StoreViewpointRequest;
use App\Http\Requests\UpdateViewpointRequest;
use App\Imports\ViewpointsImport;
use App\Models\File;
use App\Models\Viewpoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ViewpointController extends Controller
{
    public function export()
    {
        return Excel::download(new ViewpointsExport, 'vps.xlsx');
    }
    public function import(FormRequest $req)
    {
        $append = $req->input("append");
        if ($append == "off") {
            Viewpoint::truncate();
        }

        $tmp = $req->file("file");
        $hashname = $tmp->hashName();
        $tmp->storeAs(File::pf(), $hashname);
        $fullpath = storage_path(File::apf() . '/' . $hashname);
        Excel::import(new ViewpointsImport, $fullpath);
        return redirect(route('role.top', ['role' => 'pc']))->with('feedback.success', 'Viewpoint Imported !!');
    }

    /**
     * Viewpoint編集用のCRUD 2
     */
    public function itmsetting(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $tableName = 'viewpoints';
        // copy_id がセットされていたら、行をコピーする
        if ($req->has('copy_id')) {
            $copy_id = $req->input('copy_id');
            $enqitm = Viewpoint::find($copy_id);
            $newdatum = $enqitm->replicate(); // copy data
            $newdatum->orderint++;
            $newdatum->save();
        }
        // del_id がセットされていたら、行を削除する
        if ($req->has('del_id')) {
            $del_id = $req->input('del_id');
            Viewpoint::destroy($del_id);
        }
        $coldetails = AdminController::column_details($tableName);
        $coldetails['COPY'] = 'COPY';
        $ary = ['COPY', 'orderint', 'name', 'desc', 'content', 'contentafter', 'forrev', 'formeta', 'mandatory', 'hidefromrev', 'weight', 'doReturn', 'doReturnAcceptOnly','subdesc'];
        $cold2 = [];
        foreach ($ary as $f) {
            if (isset($coldetails[$f])) $cold2[$f] = $coldetails[$f];
        }
        $coldetails = $cold2;
        $title = "「" . $req->input('cat_name') . "」査読観点の編集";

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $cat_id = $whereBy['category_id'] = $req->input("cat_id");
        $tableComments = AdminController::get_table_comments($db_name, $tableName);

        // OrderInt をstep ずつで再設定する
        Viewpoint::reorderint($cat_id);
        
        $data = DB::table($tableName)->where("category_id", $req->input("cat_id"))
            ->orderBy('orderint')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->count();
        // $back_link_href = route("enq.index");
        // $back_link_label = "アンケート一覧に戻る";
        $cat_id = $req->input("cat_id");
        $cat_name = $req->input("cat_name");
        return view('admin.crudtable2')->with(compact(
            "tableName",
            "coldetails",
            "data",
            "whereBy",
            "numdata",
            "tableComments",
            "title",
            // "back_link_href",
            // "back_link_label",
            "cat_id",
            "cat_name"
        ));
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreViewpointRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Viewpoint $viewpoint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Viewpoint $viewpoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateViewpointRequest $request, Viewpoint $viewpoint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Viewpoint $viewpoint)
    {
        //
    }
}
