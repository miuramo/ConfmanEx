<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Support\Facades\Log;

class PaperObserver
{
    use MetaObserverTrait {
        created as meta_created;
    }

    /**
     * Handle to the User "created" event.
     *
     * @return void
     */
    public function created(Paper $paper)
    {
        $this->meta_created($paper);
        $paper->updateContacts();

        /**  すべてのPaperに対して、デフォルトのSubmit（査読結果）を作成する
         * デフォルトのSubmitとは、当初の投稿カテゴリに対応するもの
         *  */
        Submit::factory()->create([
            'paper_id' => $paper->id,
            'category_id' => $paper->category_id,
        ]);
        //
    }

    // /**
    //  * 更新前
    //  */
    // public function xupdating(Paper $paper)
    // {
    //     $this->meta_updating($paper);
    // }


    /**
     * Handle the Paper "updated" event.
     *
     * @return void
     */
    public function updated(Paper $paper)
    {
        $paper->updateContacts();
        //
    }

    // /**
    //  * Handle the Paper "deleted" event.
    //  *
    //  * @return void
    //  */
    // public function deleted(Paper $paper)
    // {
    //     //
    // }
}
