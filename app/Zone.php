<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zone';
    protected $primaryKey = 'iZoneId';
    public $timestamps = false;

    public static function getData(){
        $data = DB::select(DB::raw('SELECT st_astext("PShape") as geotxt, "iZoneId" FROM zone WHERE AND "iStatus" = 1 ORDER BY iZoneId'));
        dd($data);
        return $data;
    }
}
