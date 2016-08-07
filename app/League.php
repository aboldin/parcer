<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{

    protected $fillable = [
        'title', 'link', 'count', 'sport_type_id',
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Leagues';

    public function sportType()
    {
        return $this->belongsTo('App\League', 'sport_type_id');
    }

    public function matches()
    {
        return $this->hasMany('App\Match');
    }
}
