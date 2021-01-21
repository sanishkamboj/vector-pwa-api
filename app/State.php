<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'state_mas';
    protected $primaryKey = 'iStateId';
    public $timestamps = false;
}
