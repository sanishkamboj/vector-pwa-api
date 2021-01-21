<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'county_mas';
    protected $primaryKey = 'iCountyId';
    public $timestamps = false;
}
