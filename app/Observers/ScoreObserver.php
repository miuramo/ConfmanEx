<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Score;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ScoreObserver
{
    use MetaObserverTrait {
        created as meta_created;
    }

    /**
     * Handle to the User "created" event.
     *
     * @return void
     */
    public function created(Score $score)
    {
        $this->meta_created($score);
        // 対応するSubmitのスコアを修正する
        $score->submit_score_update();
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(Score $score)
    {
        // $this->meta_updated($score);
        // 対応するSubmitのスコアを修正する
        $score->submit_score_update();
    }
}
