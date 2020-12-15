<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city_mas';
    protected $primaryKey = 'iCityId';
    public $timestamps = false;
}
