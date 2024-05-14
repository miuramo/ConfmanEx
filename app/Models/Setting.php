<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait;

    protected $fillable = [
        'name',
        'value',
        'isnumber',
        'isbool',
    ];


    /**
     * Settingの REVIEWER_MEMBER や PC_MEMBERをみて、自動でロールをわりあてる
     */
    public static function auto_role_member(){
        $sets = Setting::where("name","like","%_MEMBER%")->where("valid",true)->get();
        foreach($sets as $set){
            $val = $set->value;
            // role name
            $role_name = strtolower(explode("_",$set->name)[0]);
            $role = Role::findByIdOrName($role_name);
            // | で区切る
            $ary = explode("|",$val);
            foreach($ary as $name){
                $tmpu = User::where("name",$name)->first();
                if ($tmpu == null) continue;
                if (!$role->containsUser($tmpu->id)){ // ふくまれていなければ
                    $tmpu->roles()->attach($role);
                    info("auto_role_member {$name} {$role->name}");
                }
            }
        }
    }
}
