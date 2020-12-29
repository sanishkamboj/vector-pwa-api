<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Site extends Model
{
    protected $table = 'site_mas';
    protected $primaryKey = 'iCityId';
    public $timestamps = false;

    public static function getSiteData($offset){
        $data = DB::select(DB::raw('SELECT "iSiteId" as siteid, "iSTypeId" as sTypeId, "iCityId", "iZoneId", st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas Where "iSTypeId" IN(Select "iSTypeId" FROM site_type_mas) AND "iStatus" = 1 ORDER BY siteid LIMIT 10 OFFSET '.$offset));
        //dd($data);
        return $data;
    }
}
