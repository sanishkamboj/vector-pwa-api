<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Site extends Model
{
    protected $table = 'site_mas';
    protected $primaryKey = 'iCityId';
    public $timestamps = false;

    public static function getSiteData(){
        $site_ids = DB::table('site_type_mas')->select('iSTypeId')->get();
        $sIdsArr = array();
        foreach ($site_ids as $key => $value) {
           $sIdsArr[$key] = $value->iSTypeId;
        }
        $sIds = implode(",", $sIdsArr);
        $where[] = '"iSTypeId" IN('.$sIds.')';

        $where[] ='"iStatus" = 1';

        $whereQuery = implode(" AND ", $where);

        $data = DB::select(DB::raw('SELECT "iSiteId" as siteid, "iSTypeId" as sTypeId, "iCityId", "iZoneId", st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas Where '.$whereQuery.' Limit 1,1'));
        
        return $data;
    }
}
