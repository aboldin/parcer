<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{

    const status_unchecked = 0;
    const status_works = 1;
    const status_failed = 2;
    const status_banned = 3;

    protected $fillable = [
        'status', 'proxy', 'tries'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Proxy';

}
