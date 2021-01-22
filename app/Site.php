<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Site extends Model
{
    protected $table = 'site_mas';
    protected $primaryKey = 'iSiteId';
    public $timestamps = false;

    public static function getSiteData($offset){
        $data = DB::select(DB::raw('SELECT site_mas."iSiteId" as siteid, "vName" as site_name, "iSTypeId" as sTypeId, "iCityId", site_mas."iZoneId",  site_mas."vAddress1", site_mas."vAddress2", site_mas."vStreet", site_mas."iStateId", site_mas."iCountyId", st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas Where "iSTypeId" IN(Select "iSTypeId" FROM site_type_mas) AND "iStatus" = 1 ORDER BY siteid LIMIT 100 OFFSET '.$offset));
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

    public function city(){
        return $this->belongsTo(City::class, 'iCityId');
    }

    public function country(){
        return $this->belongsTo(Country::class, 'iCountyId');
    }

    public function state(){
        return $this->belongsTo(State::class, 'iStateId');
    }
    public static function addRecord($data){
        if($data['iGeometryType'] == 2){
            $query = "INSERT INTO site_mas(\"vName\", \"iSTypeId\", \"iSSTypeId\", \"vAddress1\", \"iGeometryType\", \"vPolygonLatLong\", \"vPolyLineLatLong\", \"vLatitude\", \"vLongitude\", \"iStatus\", \"dAddedDate\") VALUES ('".$data['vName']."', ".$data['iSTypeId'].", ".$data['iSSTypeId'].", '".$data['vAddress1']."', ".$data['iGeometryType'].", ST_GeomFromText('POLYGON((".$data['vPolygonLatLong']."))', 4326), NULL, '".$data['vLatitude']."', '".$data['vLongitude']."', 1, '".$data['dAddedDate']."')";
        } else if($data['iGeometryType'] == 3){
            $query = "INSERT INTO site_mas(\"vName\", \"iSTypeId\", \"iSSTypeId\", \"vAddress1\", \"iGeometryType\", \"vPolygonLatLong\", \"vPolyLineLatLong\", \"vLatitude\", \"vLongitude\", \"iStatus\", \"dAddedDate\") VALUES ('".$data['vName']."', ".$data['iSTypeId'].", ".$data['iSSTypeId'].", '".$data['vAddress1']."', ".$data['iGeometryType'].", NULL, ST_GeomFromText('LINESTRING(".$data['vPolyLineLatLong'].")', 4326), '".$data['vLatitude']."', '".$data['vLongitude']."', 1, '".$data['dAddedDate']."')";
        }
        DB::insert($query);
    }

    public static function getSitesByDate($date){
        $data = DB::select(DB::raw('SELECT site_mas."iSiteId" as siteid, "vName" as site_name, "iSTypeId" as sTypeId, "iCityId", site_mas."iZoneId",  site_mas."vAddress1", site_mas."vAddress2", site_mas."vStreet", site_mas."iStateId", site_mas."iCountyId", st_astext(ST_Centroid("vPolygonLatLong")) as polyCenter, st_astext("vPolygonLatLong") as polygon, st_astext("vPointLatLong") as point, st_astext("vPolyLineLatLong") as poly_line FROM site_mas Where "iSTypeId" IN(Select "iSTypeId" FROM site_type_mas) AND "iStatus" = 1 AND "dAddedDate" >= \''.$date.'\' OR "dModifiedDate" >= \''.$date.'\'  ORDER BY siteid'));
        //dd($data);
        return $data;

    }
}
