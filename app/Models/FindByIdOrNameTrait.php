<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait FindByIdOrNameTrait
{
    /**
     * セキュリティ的にはどうかと思うが、便利なので
     *  AuthServiceProviderの Gate::define('role') $role_id は id数値でも nameでもよい。なんならObjectでもよい。
     * 第2引数はnull以外なら返却Objのフィールドを返す
     * 第3引数は検索するときのフィールド名。通常はname
     */
    public static function findByIdOrName(object|string $id, ?string $getfield = null, string $field = 'name')
    {
        if (is_object($id)) { // オブジェクトなら、そのまま返す
            return $id;
        } else {
            $class_name = get_class();
            if (is_string($id) || is_integer($id)) {
                $obj = $field === 'id'
                    ? $class_name::find($id)
                    : $class_name::where($field, $id)->first();
                // eval("\$obj = {$class_name}::where('$field', \$id)->first();");
                if (isset($obj)) {
                    if ($getfield == null) return $obj;
                    else return $obj->{$getfield};
                }
            }
        }
        return null;
    }
}
