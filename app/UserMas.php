<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMas extends Model
{
    protected $table = 'user_mas';
    protected $primaryKey = 'iUserId';
    public $timestamps = false;
}
