<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{

    protected $fillable = [
        'sport_type', 'league', 'match', 'profit', 'text', 'full_link', 'type', 'match_date'
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'History';

}
