<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskOther extends Model
{
    protected $table = 'task_other';
    protected $primaryKey = 'iTOId';
    public $timestamps = false;
}
