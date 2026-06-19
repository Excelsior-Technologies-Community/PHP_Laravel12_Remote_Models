<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRemoteData;



class Celebrity extends Model
{


    use HasRemoteData;



    protected $table =
    "celebrities_cache";



    protected $fillable = [

        'remote_id',
        'name',
        'birthday',
        'profession'

    ];
}
