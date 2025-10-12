<?php

namespace App\Livewire;

use App\Models\Submit;
use Livewire\Component;

/**
 * 登録者から、採択論文の一覧を出した上で、誰も登録していない採択論文を抽出する。
 */
class RegistCheckAuthor extends Component
{
    public $accepted_paper_pids = [];
    public $finished = [];
    public $papers_with_presenters = [];

    public $papers_without_presenters = [];

    public function mount()
    {
        $this->finished = \App\Models\Regist::with('user')->where('valid', 1)->orderby('created_at')->get();
        $withauthor_paper_pids = [];
        foreach( $this->finished as $reg ) {
            $withauthor_paper_pids = array_merge($withauthor_paper_pids, $reg->user->accepted_papers_as_any());
        }

        $this->accepted_paper_pids = Submit::with('paper')->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderby('category_id')->orderby('paper_id')->get()->pluck("paper_id")->toArray();

        $this->papers_without_presenters = array_diff($this->accepted_paper_pids, $withauthor_paper_pids);

    }

    public function render()
    {
        return view('livewire.regist-check-author');
    }
}
