<?php

namespace App\Http\Controllers;

use App\Exports\VoteAnswersExport;
use App\Http\Requests\StorevotesRequest;
use App\Http\Requests\UpdatevotesRequest;
use App\Models\Submit;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VoteItem;
use App\Models\VoteTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class VoteController extends Controller
{
    /**
     * 案内表示
     */
    public function index(Request $req)
    {
        // if ($req->method() === 'POST') {
        //     // info($req->all());
        //     if (strlen($req->input("sssname")) < 3 ||   strlen($req->input("sssaffil")) < 2) {
        //         return redirect('/vote')->with('feedback.error', '氏名と所属の両方を入力してください。');
        //     }
        //     $formData = json_encode($req->all());
        //     $minutes = 60 * 24 * 3; // 3日間有効なクッキー
        //     Cookie::queue('formData', $formData, $minutes);
        //     return redirect('/vote')->with('feedback.success', '氏名と所属を一時保存しました。');
        // }
        if (Auth::check()) {
            $uid = auth()->id();
            $ticket = VoteTicket::where('user_id', $uid)->where('activated', true)->where('valid', true)->first();
            if (!$ticket) {
                abort(403, 'Vote ticket not found or not activated.');
            }
        } else {
            $cookie_token = json_decode(Cookie::get('vote_ticket_token'), true);
            $ticket = VoteTicket::where('token', $cookie_token)->where('activated', true)->where('valid', true)->first();
            if (!$ticket) {
                abort(403, 'Vote ticket not found or not activated.');
            }
        }
        return view("vote.index")->with(compact("ticket"));
    }
    /**
     * 投票権の有効化（token付きのURL）
     */
    public function activate(string $token)
    {
        $ticket = VoteTicket::where('token', $token)->first();
        if (!$ticket) {
            return redirect('/vote')->with('feedback.error', '無効なトークンです。');
        }
        if ($ticket->valid) {
            $ticket->activated = true;
            if (Auth::check()) {
                $ticket->user_id = auth()->id();
            } else {
                $minutes = 60 * 24 * 3; // 3日間有効なクッキー
                Cookie::queue('vote_ticket_token', $token, $minutes);
            }
            $ticket->save();
            return redirect('/vote')->with('feedback.success', '投票権が有効化されました。');
        }
        $ticket->valid = true;
        $ticket->save();
        return redirect('/vote')->with('feedback.success', '投票権が有効化されました。');
    }
    public function vote(Request $req, Vote $vote)
    {
        if (Auth::check()) {
            $uid = auth()->id();
            $formData = ["uid" => $uid, "_token" => "uid_" . $uid];
        } else {
            $uid = null;
            $formData = json_decode(Cookie::get('formData'), true);
        }
        if (!$formData) return redirect('/vote')->with('feedback.error', '投票のまえに、氏名と所属を入力してください。');
        if (!$vote->isopen || $vote->isclose) {
            return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
        }
        if ($req->method() === 'POST') {
            if (!$vote->isopen || $vote->isclose) {
                return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
            }
            // info($req->all());
            // 一旦、これまでのデータをすべて消す。
            DB::transaction(function () use ($vote, $uid, $formData) {
                VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $formData) {
                    $query->where("user_id", $uid)->orWhere("token", $formData['_token']);
                })->delete();
            });
            $subbooth2id = Submit::select("id", "booth")->get()->pluck("id", "booth")->toArray();
            // info($subbooth2id);
            $student_boothes = VoteItem::student_boothes();
            foreach ($req->all() as $booth => $val) {
                if ($val == 'on') {
                    VoteAnswer::firstOrCreate([
                        'user_id' => $uid,
                        'token' => $formData['_token'],
                        'submit_id' => $subbooth2id[$booth],
                        'valid' => (isset($student_boothes[$booth]) ? 2 : 1),
                    ], [
                        'comment' => $req->input('comment'),
                        'vote_id' => $vote->id,
                        'booth' => $booth,
                    ]);
                }
            }

            return redirect()->route('vote.vote', ['vote' => $vote])->with('feedback.success', '投票結果を保存しました。');
        }
        // チェック再現のため、保存データを取得する
        $vas = VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $formData) {
            $query->where("user_id", $uid)->orWhere("token", $formData['_token']);
        })->get()->pluck("submit_id", "booth")->toArray();
        return view("vote.vote")->with(compact("vote", "formData", "uid", "vas"));
    }

    public function download_answers()
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);

        return Excel::download(new VoteAnswersExport(), "投票結果.xlsx");
    }

    /**
     * すべてリセットする
     */
    public function resetall($isclose = 0)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        VoteAnswer::truncate();
        VoteItem::truncate();
        Vote::truncate();

        Vote::init($isclose);
        VoteItem::init();
        return redirect()->route('role.top', ['role' => 'award']);
    }

    public function create_tickets(Request $req)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        if (!$req->has('emails')) {
            $emails = ["miuramo@gmail.com"];
            // return redirect()->route('role.top',['role'=>'award'])->with('feedback.error', 'メールアドレスを入力してください。');
        } else {
            $emails = explode("\n", $req->input('emails'));
        }
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
        // VoteTicket::truncate();
        foreach ($emails as $em) {
            $token = Str::random(30);
            $ticket = VoteTicket::create([
                'email' => $em,
                'token' => $token,
                'token_hash' => hash('sha256', $token),
            ]);
        }
        return redirect()->route('role.top', ['role' => 'award'])->with('feedback.success', '投票チケットを作成しました');
    }

    public function send_tickets()
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        $tickets = VoteTicket::where('activated', true)->where('valid', true)->get();
        if ($tickets->isEmpty()) {
            return redirect()->route('role.top', ['role' => 'award'])->with('feedback.error', '有効な投票チケットがありません。');
        }
        foreach ($tickets as $ticket) {
            Mail::to($ticket->email)->send(new \App\Mail\VoteTicketMail($ticket));
            $ticket->activated = true;
            $ticket->save();
        }
        return redirect()->route('role.top', ['role' => 'award'])->with('feedback.success', '投票チケットをメール送信しました。');
    }
}
