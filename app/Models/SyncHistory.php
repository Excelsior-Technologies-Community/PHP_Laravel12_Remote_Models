<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncHistory extends Model
{
    protected $fillable = [

        'records_count',
        'synced_at'

    ];
}