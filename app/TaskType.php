<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $table = 'task_type_mas';
    protected $primaryKey = 'iTaskTypeId';
    public $timestamps = false;
}
