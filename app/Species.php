<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    protected $table = 'mosquito_species_mas';
    protected $primaryKey = 'iMSpeciesId';
    public $timestamps = false;
}
