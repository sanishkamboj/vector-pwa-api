<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskTrap extends Model
{
    protected $table = 'task_trap';
    protected $primaryKey = 'iTTId';
    public $timestamps = false;
}
