<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{

    protected $fillable = [
        'title', 'link', 'full_link', 'league_id', 'link_id', 'match_date'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Matches';

    public function league()
    {
        return $this->belongsTo('App\League', 'league_id');
    }

    public function profits()
    {
        return $this->hasMany('App\Profits');
    }
}
