<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEntryRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Mail\Mailer;
use App\Mail\FirstInvitation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function entry0()
    {
        return view("user/entry");
    }

    public function entry(UserEntryRequest $req)
    {
        // $em = $req->input("email");
        return $req->shori();
    }
    public function profile(Request $request): View
    {
        return view("user.profile", [
            'user' => $request->user(),
        ]);
    }

    public function search(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        if ($req->has('query')) {
            $keyword = $req->input('query');
            $query = User::with('papers')->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('affil', 'like', "%{$keyword}%")
                    ->orWhere('id', $keyword)
                    ->orWhere('email', 'like', "{$keyword}%");
            });
            // $query = DB::table('users');
            // $query->where(function ($subQuery) use ($keyword) {
            //     $subQuery->where('name', 'like', "%{$keyword}%")
            //         ->orWhere('affil', 'like', "%{$keyword}%")
            //         ->orWhere('id', $keyword)
            //         ->orWhere('email', 'like', "{$keyword}%");
            // });

            $results = $query->orderBy('affil')->get();
            return response()->json(['u' => $results, 'id' => auth()->id()]);
        }
    }
}
