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
use Illuminate\Support\Facades\Request;
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
        $fullpath = storage_path(File::apf() .'/'. $hashname);
        Excel::import(new ViewpointsImport, $fullpath);
        return redirect(route('role.top', ['role' => 'pc']))->with('feedback.success', 'Viewpoint Imported !!');
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
