<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FootballProfit extends Model
{

    protected $fillable = [
        'type', 'profit', 'text', 'football_match_id'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'FootballProfits';

    public function match()
    {
        return $this->belongsTo('App\FootballMatch');
    }
}
