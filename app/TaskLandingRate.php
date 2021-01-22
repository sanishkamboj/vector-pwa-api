<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskLandingRate extends Model
{
    protected $table = 'task_landing_rate';
    protected $primaryKey = 'iTLRId';
    public $timestamps = false;
}
