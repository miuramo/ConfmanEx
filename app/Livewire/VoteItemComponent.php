<?php

namespace App\Livewire;

use App\Models\Paper;
use App\Models\Submit;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VoteItem;
use App\Models\VoteTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VoteItemComponent extends Component
{
    public VoteItem $voteItem;
    public Vote $vote;
    public $papers = [];
    public $authors = [];
    public array $selectedPapers = [];
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
        sort($this->selectedPapers);

        DB::transaction(function () {
            VoteAnswer::where("vote_id", $this->vote->id)->where(function ($query) {
                $query->where("user_id", $this->uid)->orWhere("token", $this->ticket->token);
            })->delete();
        });
        $subbooth2id = Submit::select("id", "booth")->get()->pluck("id", "booth")->toArray();
        DB::transaction(function () use ($subbooth2id) {
            foreach ($this->selectedPapers as $n => $booth) {
                VoteAnswer::firstOrCreate([
                    'user_id' => $this->uid,
                    'token' => $this->ticket->token,
                    'submit_id' => $subbooth2id[$booth],
                    'valid' => 1, // (isset($student_boothes[$booth]) ? 2 : 1),
                ], [
                    'comment' => $this->comment,
                    'vote_id' => $this->vote->id,
                    'booth' => $booth,
                ]);
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
        $this->selectedPapers = VoteAnswer::where('token', $this->ticket->token)->where('vote_id', $this->vote->id)->pluck('booth')->toArray();
    }

    public function render()
    {
        return view('livewire.vote-item-component', [
            'vi' => $this->voteItem,
            'vote' => $this->vote,
        ]);
    }
}
