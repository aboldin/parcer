<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{

    protected $fillable = [
        'title', 'link', 'football_league_id', 'link_id'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'FootballMatches';

    public function league()
    {
        return $this->belongsTo('App\FootballLeague');
    }

    public function profits()
    {
        return $this->hasMany('App\FootballProfits');
    }
}
