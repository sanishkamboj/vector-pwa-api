<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class SrDetail extends Model
{
    protected $table = 'sr_details';
    protected $primaryKey = 'iSRId';
    public $timestamps = false;

    public static function getSRData($id){
        if($id != ""){
            $where[] = ' sr_details."iSRId" IN ('.$id.')'; 
        }
        $where[] = ' sr_details."iStatus" != 4 ';
        $whereQuery = implode(" AND ", $where);
        $data = DB::select(DB::raw('SELECT sr_details.*,contact_mas."vFirstName",concat(user_mas."vFirstName",\' \', user_mas."vLastName") AS "vAssignTo" FROM "public"."sr_details" left join contact_mas on "contact_mas"."iCId" = "sr_details"."iCId" LEFT JOIN state_mas  on "state_mas"."iStateId" = "sr_details"."iStateId" LEFT JOIN "city_mas" on "city_mas"."iCityId" = "sr_details"."iCityId" LEFT JOIN user_mas on user_mas."iUserId" = sr_details."iUserId" WHERE  '.$whereQuery.' ORDER BY sr_details."iSRId"'));
        //dd($data);
        return $data;
    }
}
