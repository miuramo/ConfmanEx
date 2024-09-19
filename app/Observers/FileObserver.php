<?php

namespace App\Observers;

use App\Jobs\PdfJob;
use App\Models\Contact;
use App\Models\File;
use App\Models\Paper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FileObserver
{
    use MetaObserverTrait {
        deleted as meta_deleted;
    }
    // /**
    //  * Handle to the File "created" event.
    //  *
    //  * @return void
    //  */
    // public function created(File $file)
    // {
    // }

    // /**
    //  * Handle the File "updated" event.
    //  *
    //  * @return void
    //  */
    // public function updated(File $file)
    // {
    //     //
    // }

    /**
     * Handle the File "deleted" event.
     *
     * @return void
     */
    public function deleted(File $file)
    {
        $this->meta_deleted($file);
        // Log::info("[FileObserver@deleted] ", ["file" => $file]);
        DB::transaction(function () use ($file) {
            $paper = Paper::find($file->paper_id);
            if ($paper->pdf_file_id != null && $paper->pdf_file_id == $file->id) {
                $paper->pdf_file_id = null;
                $paper->save();
            }
            if ($paper->img_file_id != null && $paper->img_file_id == $file->id) {
                $paper->img_file_id = null;
                $paper->save();
            }
            if ($paper->video_file_id != null && $paper->video_file_id == $file->id) {
                $paper->video_file_id = null;
                $paper->save();
            }
            if ($paper->altpdf_file_id != null && $paper->altpdf_file_id == $file->id) {
                $paper->altpdf_file_id = null;
                $paper->save();
            }
        });
    }
}
