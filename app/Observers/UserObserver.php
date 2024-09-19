<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    use MetaObserverTrait {
        created as meta_created;
    }

    /**
     * Handle to the User "created" event.
     *
     * @return void
     */
    public function created(User $user)
    {
        $this->meta_created($user);

        DB::transaction(function () use ($user) {
            $con = Contact::firstOrCreate([
                'email' => $user->email,
            ]);
            $user->contact_id = $con->id;
            $user->save();
        });
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(User $user)
    {
        if ($user->contact_id == 0) {
            $this->created($user);
        } else {
            DB::transaction(function () use ($user) {
                $con = Contact::firstOrCreate([
                    'email' => $user->email,
                ]);
                if ($con->id != $user->contact_id) {
                    $user->contact_id = $con->id;
                    $user->save();
                }
            });
            Setting::auto_role_member(); // 設定をみて、自動でロールをわりあてる。
        }
        //
    }
}
