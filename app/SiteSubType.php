<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteSubType extends Model
{
    protected $table = 'site_sub_type_mas';
    protected $primaryKey = 'iSSTypeId';
    public $timestamps = false;

    public function site_type(){
        return $this->belongsTo(SiteType::class, 'iSTypeId', 'iSiteTypeId');
    }
}
