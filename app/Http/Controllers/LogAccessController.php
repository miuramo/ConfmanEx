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

    

    

    

    

    

    
}
