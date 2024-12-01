<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait GetValueTrait
{

    public static function isTrue(object|string $id)
    {
        $obj = self::findByIdOrName($id);
        if ($obj == null) return false;
        if ($obj->valid == false) return false;
        if ($obj->value == 'true') return true;
        return false;
    }
    public static function getValue(object|string $id)
    {
        $obj = self::findByIdOrName($id);
        if ($obj == null) return null;
        if ($obj->valid == false) return null;
        if (isset($obj->value)) return $obj->value;
        return null;
    }
    public static function getObj(object|string $id)
    {
        $obj = self::findByIdOrName($id);
        if ($obj == null) return null;
        if ($obj->valid == false) return null;
        return $obj;
    }

}
