<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrapType extends Model
{
    protected $table = 'trap_type_mas';
    protected $primaryKey = 'iTrapTypeId';
    public $timestamps = false;
}
