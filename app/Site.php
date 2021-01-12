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
        $data = DB::select(DB::raw('SELECT site_mas."iSiteId" as siteid, "vName" as site_name, "iSTypeId" as sTypeId, "iCityId", "iZoneId",  st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas Where "iSTypeId" IN(Select "iSTypeId" FROM site_type_mas) AND "iStatus" = 1 ORDER BY siteid LIMIT 1000 OFFSET '.$offset));
        //dd($data);
        return $data;
    }

    public static function getSitesByAddress($param){
        $siteIds = [];
        $site_data = [];
        $data = DB::select(DB::raw('SELECT "iSiteId" FROM site_mas WHERE "vLatitude" = '.$param['lat']. 'AND "vLongitude" = '.$param['lng'].' AND "iStatus" = 1'));
        foreach($data as $site){
            $siteIds[] = $site->iSiteId;
        }
        if(count($siteIds)){
            $ids = implode(',', $siteIds);
            $site_data = DB::select(DB::raw('SELECT site_mas."iSiteId" as siteid, "vName" as site_name, "iSTypeId" as sTypeId, "iCityId", "iZoneId",  st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas WHERE "iSiteId" IN ('.$ids.')'));
        } 
        
        return $site_data;
    }

    public static function getSiteById($id){
        $site_data = DB::select(DB::raw('SELECT site_mas."iSiteId" as siteid, "vName" as site_name, "iSTypeId" as sTypeId, "iCityId", "iZoneId",  st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas WHERE "iSiteId"  = '. $id));

        return $site_data;
    }
}
