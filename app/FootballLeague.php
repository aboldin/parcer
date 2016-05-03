<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FootballLeague extends Model
{

    protected $fillable = [
        'title', 'link', 'count',
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'FootballLeagues';

    public function matches()
    {
        return $this->hasMany('App\FootballMatch');
    }
}
