<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TreatmentProduct extends Model
{
    protected $table = 'treatment_product';
    protected $primaryKey = 'iTPId';
    public $timestamps = false;
}
