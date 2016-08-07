<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SportType extends Model
{

    protected $fillable = [
        'id', 'name', 'url',
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'SportTypes';

    public function leagues()
    {
        return $this->hasMany('App\League');
    }
}
