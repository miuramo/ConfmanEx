<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Contact extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait;

    protected $fillable = [
        'email',
    ];

    public function papers()
    {
        $tbl = 'paper_contact';
        // $table_fields = Schema::getColumnListing($tbl);
        // return $this->belongsToMany(User::class, $tbl, 'role_id', 'user_id');// ->withPivot($table_fields)->using(RolesUser::class);
        return $this->belongsToMany(Paper::class, $tbl);//->using(RolesUser::class);
    }

}
