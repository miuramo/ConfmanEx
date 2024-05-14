<?php

namespace App\Observers;

use App\Models\LogCreate;
use App\Models\LogModify;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait MetaObserverTrait
{

    /**
     * 更新前
     */
    public function updating(Model $model)
    {
        $class_name = get_class($model);
        eval("\$current = {$class_name}::find(\$model->id);");
        $changes = $model->getDirty();
        $diffary = [];
        foreach ($changes as $field => $newval) {
            if (isset($current) && $current->{$field} != $newval) {
                if (is_array($newval)) $newval = json_encode($newval);
                if (is_array($current->{$field})) $current->{$field} = json_encode($current->{$field});
                $diffary[] = "{$field} : {$current->{$field}} → {$newval}";
            }
        }
        $diffstr = implode("\n", $diffary);

        //ログインユーザ
        $uid = (Auth::user() == null) ? 0 : Auth::user()->id;

        LogModify::create(
            [
                'uid' => $uid,
                'table' => strtolower(substr($class_name, 11)),
                'target_id' => $model->id,
                'diff' => $diffstr,
            ]
        );
    }

    public function created(Model $model)
    {
        $class_name = get_class($model);

        //ログインユーザ
        $uid = (Auth::user() == null) ? 0 : Auth::id();

        LogCreate::create(
            [
                'uid' => $uid,
                'table' => strtolower(substr($class_name, 11)),
                'target_id' => $model->id,
                'data' => $model,
            ]
        );
    }

    /**
     * Handle the File "deleted" event.
     *
     * @return void
     */
    public function deleted(Model $model)
    {
        // Log::info("META deleted: " . get_class($model));
        $class_name = get_class($model);
        //ログインユーザ
        $uid = (Auth::user() == null) ? 0 : Auth::id();
        LogCreate::create(
            [
                'uid' => $uid,
                'table' => strtolower(substr($class_name, 11)),
                'target_id' => $model->id,
                'data' => '{"deleted":"deleted"}'
            ]
        );
    }
}
