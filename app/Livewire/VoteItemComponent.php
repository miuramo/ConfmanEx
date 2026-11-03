<?php

namespace App\Livewire;

use App\Models\Paper;
use App\Models\Submit;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VoteItem;
use App\Models\VoteTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VoteItemComponent extends Component
{
    public VoteItem $voteItem;
    public Vote $vote;
    public $papers = [];
    public $authors = [];
    public array $selectedPapers = [];
    public array $selfBooths = [];
    public VoteTicket $ticket;
    public $uid;
    public $comment = '';

    public function checkLimit()
    {
        // 最大数を超えたら、最後に選択されたものを外す
        if ($this->voteItem->upperlimit > 0 && count($this->selectedPapers) > $this->voteItem->upperlimit) {
            array_pop($this->selectedPapers);
        }
        sort($this->selectedPapers);
    }

    public function updated($propertyName, $value)
    {
        // info("Property updated: {$propertyName} with value: {$value}");
        // 自著への投票が禁止されている場合、自分の発表にあたる booth は除外する
        if ($this->vote->prohibit_selfvote && !empty($this->selfBooths)) {
            $this->selectedPapers = array_values(array_diff($this->selectedPapers, $this->selfBooths));
        }
        sort($this->selectedPapers);

        DB::transaction(function () {
            VoteAnswer::where("valid", 1)->where("vote_id", $this->vote->id)->where("vote_item_id", $this->voteItem->id)->where(function ($query) {
                $query->where("user_id", $this->uid)->orWhere("token", $this->ticket->token);
            })->update([
                'valid' => 0, // 既存の回答を無効化
            ]);
        });
        $subbooth2id = Submit::select("id", "booth")->get()->pluck("id", "booth")->toArray();
        DB::transaction(function () use ($subbooth2id) {
            foreach ($this->selectedPapers as $n => $booth) {
                $target = VoteAnswer::firstOrCreate([
                    'token' => $this->ticket->token,
                    'submit_id' => $subbooth2id[$booth],
                    'booth' => $booth,
                    'vote_id' => $this->vote->id,
                    'vote_item_id' => $this->voteItem->id,
                ], [
                    'user_id' => $this->uid,
                    'comment' => $this->comment,
                    'valid' => 1, // (isset($student_boothes[$booth]) ? 2 : 1),
                ]);
                if ($target->valid == 0) {
                    // 既存の回答が無効化されていた場合、有効にする
                    $target->valid = 1;
                    $target->save();
                }
            }
        });
    }

    public function mount()
    {
        $this->papers = Paper::select('title', 'id')->pluck('title', 'id')->toArray();
        $this->authors = Paper::select('authorlist', 'id')->pluck('authorlist', 'id')->toArray();

        if (Auth::check()) {
            $this->uid = auth()->id();
            $this->ticket = VoteTicket::where('user_id', $this->uid)->where('activated', true)->where('valid', true)->first();
            if (!$this->ticket) {
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、こちらの投票ページに遷移してください。');
            }
            $this->comment = auth()->user()->name . ' ' . auth()->user()->affil;
        } else {
            $this->uid = null;
            $cookie_token = Cookie::get('vote_ticket_token');
            $this->ticket = VoteTicket::where('token', $cookie_token)->where('activated', true)->where('valid', true)->first();
            if (!$this->ticket) {
                return view('vote.vote_error')->with('reason', 'メールで届く投票URLをクリックしてから、同じブラウザで、こちらの投票ページに遷移してください。');
            }
            $this->comment = $this->ticket->email;
        }
        $this->selectedPapers = VoteAnswer::where('valid', 1)->where('vote_id', $this->vote->id)->where('vote_item_id', $this->voteItem->id)->where('token', $this->ticket->token)->pluck('booth')->toArray();

        // 自著への投票が禁止されている場合、自分の発表にあたる booth を特定する（getATが0以下なら投票可能）
        $this->selfBooths = [];
        if ($this->vote->prohibit_selfvote) {
            $uid = $this->uid;
            if ($uid === null) {
                // 未ログインの場合は、投票チケットのメールアドレスからユーザーを特定する
                $uid = User::where('email', $this->ticket->email)->value('id');
            }
            if ($uid) {
                $boothes = json_decode($this->voteItem->submits, true);
                if (is_array($boothes)) {
                    foreach ($boothes as $booth => $pid) {
                        if (Paper::getAT($uid, $pid) > 0) {
                            $this->selfBooths[] = $booth;
                        }
                    }
                }
                // 既存の選択済み回答からも除外する（無効なチェックボックスが選択状態になるのを防ぐ）
                $this->selectedPapers = array_values(array_diff($this->selectedPapers, $this->selfBooths));
            }
        }
    }

    public function render()
    {
        return view('livewire.vote-item-component', [
            'vi' => $this->voteItem,
            'vote' => $this->vote,
        ]);
    }
}
