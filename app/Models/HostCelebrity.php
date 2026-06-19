<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class HostCelebrity extends Model
{

    protected $table = 'celebrities';


    protected $fillable = [

        'name',
        'birthday',
        'profession'

    ];

}