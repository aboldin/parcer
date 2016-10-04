<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Log extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'response', 'proxy_id'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Log';

    public function proxy()
    {
        return $this->belongsTo('App\Proxy', 'proxy_id');
    }

}
