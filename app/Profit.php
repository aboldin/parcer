<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{

    protected $fillable = [
        'type', 'profit', 'text', 'match_id'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Profits';

    public function match()
    {
        return $this->belongsTo('App\Match', 'match_id');
    }
}
