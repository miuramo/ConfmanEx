<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnqueteItem extends Model
{
    use HasFactory;

    public function enquete()
    {
        return $this->belongsTo(Enquete::class);
    }

    /**
     * バリデーション
     */
    public function validate_rule(string $val)
    {
        if ($val == null) return true;
        if ($this->pregrule == null) return true;
        if (is_numeric($val)) $val = strval($val);
        $val = mb_convert_kana($val, 'a', 'UTF-8');
        return preg_match($this->pregrule, $val);
    }
}
