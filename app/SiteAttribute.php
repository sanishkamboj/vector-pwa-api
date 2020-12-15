<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteAttribute extends Model
{
    protected $table = 'site_attribute_mas';
    protected $primaryKey = 'iSAttributeId';
    public $timestamps = false;
}
