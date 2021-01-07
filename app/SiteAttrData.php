<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteAttrData extends Model
{
    protected $table = 'site_attribute';
    protected $primaryKey = 'iSAId';
    public $timestamps = false;
}
