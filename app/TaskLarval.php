<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskLarval extends Model
{
    protected $table = 'task_larval_surveillance';
    protected $primaryKey = 'iTLSId';
    public $timestamps = false;
}
