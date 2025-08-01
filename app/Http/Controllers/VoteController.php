<?php

namespace App\Http\Controllers;

use App\Exports\VoteAnswersExport;
use App\Http\Requests\StorevotesRequest;
use App\Http\Requests\UpdatevotesRequest;
use App\Mail\VoteTicketEmail;
use App\Models\Submit;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VoteItem;
use App\Models\VoteTicket;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

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
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、こちらの投票ページに遷移してください。');
                // abort(403, 'メールで届く投票URLをクリックしてから、こちらの投票ページに遷移してください。');
            }
        } else {
            $cookie_token = Cookie::get('vote_ticket_token');
            $ticket = VoteTicket::where('token', $cookie_token)->where('activated', true)->where('valid', true)->first();
            if (!$ticket) {
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、同じブラウザで、こちらの投票ページに遷移してください。');
                // abort(403, 'メールで届く投票URLをクリックしてから、同じブラウザで、こちらの投票ページに遷移してください。');
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
            return view('vote.activate_error')->with('reason', '無効なトークンです。');
        }
        // if ($ticket->activated) {
        //     return view('vote.activate_error')->with('reason', 'この投票トークンはすでに有効化されています。');
        // }
        if ($ticket->valid) {
            if (Auth::check()) {
                // すでに投票トークンが紐づけられている場合は、有効化をキャンセルする
                $existingTicket = VoteTicket::where('user_id', auth()->id())->where('activated', true)->where('valid', true)->first();
                if ($existingTicket) {
                    if ($existingTicket->token !== $token) {
                        return view('vote.activate_error')->with('reason', '現在ログインしているアカウントには、すでに別の投票権が紐づけられています。そのため、この投票トークンを有効化することはできません。');
                    } else {
                        // 以前と同じトークンなので、そのまま投票ページに遷移する
                        return redirect('/vote'); // ->with('feedback.success', '注：この投票トークンは以前有効化されています。');
                    }
                }
                // ユーザーIDを設定して有効化
                $ticket->user_id = auth()->id();
            } else {
                info("Cookieからトークンを取得 " . $token);
                $minutes = 60 * 24 * 3; // 3日間有効なクッキー
                Cookie::queue('vote_ticket_token', $token, $minutes);
            }
            $ticket->activated = true;
            $ticket->save();
        }
        return redirect('/vote')->with('feedback.success', '投票権が有効化されました。');
    }
    // public function activate_error()
    // {
    //     return view('vote.activate_error');
    // }
    public function vote(Request $req, Vote $vote)
    {
        if (Auth::check()) {
            $uid = auth()->id();
            $ticket = VoteTicket::where('user_id', $uid)->where('activated', true)->where('valid', true)->first();
            if (!$ticket) {
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、こちらの投票ページに遷移してください。');
                // abort(403, 'メールで届く投票URLをクリックしてから、こちらの投票ページに遷移してください。');
            }
        } else {
            $uid = null;
            $cookie_token = Cookie::get('vote_ticket_token');
            $ticket = VoteTicket::where('token', $cookie_token)->where('activated', true)->where('valid', true)->first();
            if (!$ticket) {
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、同じブラウザで、こちらの投票ページに遷移してください。');
                // abort(403, 'メールで届く投票URLをクリックしてから、同じブラウザで、こちらの投票ページに遷移してください。');
            }
        }
        if (!$vote->isopen || $vote->isclose) {
            return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
        }
        if ($req->method() === 'POST') {
            if (!$vote->isopen || $vote->isclose) {
                return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
            }
            // info($req->all());
            // 一旦、これまでのデータをすべて消す。
            DB::transaction(function () use ($vote, $uid, $ticket) {
                VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $ticket) {
                    $query->where("user_id", $uid)->orWhere("token", $ticket->token);
                })->delete();
            });
            $subbooth2id = Submit::select("id", "booth")->get()->pluck("id", "booth")->toArray();

            // $student_boothes = VoteItem::student_boothes();
            foreach ($req->all() as $booth => $val) {
                if ($val == 'on') {
                    VoteAnswer::firstOrCreate([
                        'user_id' => $uid,
                        'token' => $ticket->token,
                        'submit_id' => $subbooth2id[$booth],
                        'valid' => 1,// (isset($student_boothes[$booth]) ? 2 : 1),
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
        $vas = VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $ticket) {
            $query->where("user_id", $uid)->orWhere("token", $ticket->token);
        })->get()->pluck("submit_id", "booth")->toArray();
        return view("vote.vote")->with(compact("vote", "ticket", "uid", "vas"));
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
        if ($req->has('emails')) {
            $emails = explode("\n", $req->input('emails'));
            $emails = array_map('trim', $emails);
            $emails = array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
            // VoteTicket::truncate();
            // 重複していたら作成しない。
            foreach ($emails as $em) {
                $token = Str::random(30);
                try {
                    $ticket = VoteTicket::create([
                        'email' => $em,
                        'token' => $token,
                        'token_hash' => hash('sha256', $token),
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    // 既に存在する場合はスキップ
                    continue;
                }
            }
            return back()->with('feedback.success', '投票チケットを作成しました');
        }
        return view('vote.create_tickets')->with([
            'emails' => '',
        ]);
    }

    /**
     * 有効なチケットをメール送信する
     */
    public function send_tickets()
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        $tickets = VoteTicket::where('valid', true)->get();
        if ($tickets->isEmpty()) {
            return redirect()->route('role.top', ['role' => 'award'])->with('feedback.error', '有効な投票チケットがありません。');
        }
        foreach ($tickets as $ticket) {
            (new VoteTicketEmail($ticket))->process_send();
        }
        return back()->with('feedback.success', '投票チケットをメール送信しました。');
    }

    /**
     * チェックされたチケットをメール送信する
     */
    public function send_tickets_checked(Request $req)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        if ($req->input('action') === 'chkdestroy') { // chksend なら 送信
            return $this->destroy_tickets_checked($req);
        }
        $ticket_ids = $req->input('ticket_ids', []);
        if (empty($ticket_ids)) {
            return redirect()->route('vote.create_tickets')->with('feedback.error', '送信するチケットを選択してください。');
        }
        $tickets = VoteTicket::whereIn('id', $ticket_ids)->where('valid', true)->get();
        if ($tickets->isEmpty()) {
            return redirect()->route('vote.create_tickets')->with('feedback.error', '選択されたチケットは有効ではありません。');
        }
        foreach ($tickets as $ticket) {
            (new VoteTicketEmail($ticket))->process_send();
        }
        return back()->with('feedback.success', '選択されたチケットをメール送信しました。');
    }

    public function destroy_tickets(Request $req)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        VoteTicket::truncate();
        return redirect()->route('vote.create_tickets')->with('feedback.success', '既存の投票チケットを削除しました');
    }
    public function destroy_tickets_checked(Request $req)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        $ticket_ids = $req->input('ticket_ids', []);
        if (empty($ticket_ids)) {
            return redirect()->route('vote.create_tickets')->with('feedback.error', '削除するチケットを選択してください。');
        }
        VoteTicket::whereIn('id', $ticket_ids)->delete();
        return redirect()->route('vote.create_tickets')->with('feedback.success', '選択された投票チケットを削除しました');
    }

}