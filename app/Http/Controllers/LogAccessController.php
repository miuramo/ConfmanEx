<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogAccessRequest;
use App\Http\Requests\UpdateLogAccessRequest;
use App\Models\LogAccess;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LogAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($user = null)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) {
            abort(403, 'Unauthorized action.');
        }
        if ($user) {
            $logs = LogAccess::where('uid', $user)->whereNot('url', '')->latest()->paginate(1000);
        } else {
            $logs = LogAccess::whereNot('url', '')->latest()->paginate(1000);
        }
        $users = User::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        // 最近ログインしたユーザを取得
        $recentuids = DB::table('log_accesses')
            ->distinct()
            ->where('uid', '>', 0)
            ->pluck('uid');
        $recentusers = User::whereIn('id', $recentuids)->get()->pluck('name', 'id')->toArray();

        return view('log_access.index', compact('logs', 'user', 'users', 'recentusers'));
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
    public function store(StoreLogAccessRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LogAccess $logAccess)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LogAccess $logAccess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLogAccessRequest $request, LogAccess $logAccess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LogAccess $logAccess)
    {
        //
    }
}
