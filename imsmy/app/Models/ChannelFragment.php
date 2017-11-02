<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelFragment extends Model
{
    //
    protected $table = 'channel_fragment';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id' ,
        'channel_id',
        'fragment_id',
        'time_add',
        'time_update',
        'fragment_temporary_id'
    ];

    public $timestamps = false;
}
