<?php

namespace App\Livewire;

use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Mail\Markdown;

class PreviewMailTemplate extends Component
{
    public $type;
    public $id;
    public $target_item;
    public MailTemplate $mt;

    public $subject;
    public $markdown;

    public function mount(MailTemplate $mt, $type, $id)
    {
        $this->mt = $mt;
        $this->type = $type;
        $this->changeId($id);
    }
    public function render()
    {
        return view('livewire.preview-mail-template');
    }

    #[On('id-changed')]
    public function changeId(int $value): void
    {
        $this->id = $value;
        if ($this->type === 'paper') {
            $this->target_item = Paper::find($this->id);
        } elseif ($this->type === 'user') {
            $this->target_item = User::find($this->id);
        }

        $replacetxt = $this->mt->getreplacetxt($this->target_item);
        $this->markdown = (string) Markdown::parse($this->mt->make_body($replacetxt));
        $this->subject = $this->mt->make_subject($replacetxt);
    }
}
