<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteType extends Model
{
    protected $table = 'site_type_mas';
    protected $primaryKey = 'iSiteTypeId';
    public $timestamps = false;

    public function sub_types(){
        return $this->hasMany(SiteSubType::class, 'iSTypeId', 'iSiteTypeId');
    }
}
