<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskTreatment extends Model
{
    protected $table = 'task_treatment';
    protected $primaryKey = 'iTreatmentId';
    public $timestamps = false;
}
