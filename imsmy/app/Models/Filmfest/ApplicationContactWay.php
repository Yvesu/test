<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class ApplicationContactWay extends Model
{
    //
    protected $table = 'application_contact_way';

    protected $priamrykey = 'id';

    protected $fillable = [
        'application_id','contact_way','time_add','time_update','type'
    ];

    public $timestamps = false;
}
