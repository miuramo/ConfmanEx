<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEntryRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Mail\Mailer;
use App\Mail\FirstInvitation;
use Illuminate\Contracts\View\View;
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
    public function profile(Request $request) : View
    {
        return view("user.profile", [
            'user' => $request->user(),
        ]);
    }

}
