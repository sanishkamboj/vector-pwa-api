<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Auth;
use Config;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function switchDatabase($user_db = null, $connection = 'mysql')
    {
        $db = !is_null($user_db) ? $user_db : env("DB_DATABASE");
        $config = Config::set("database.connections." . $connection . ".database", $db);        
        Config::set("database.default", $connection);
        try {
            $connection_response = DB::reconnect($connection);
           // print_r($connection_response); die;
            return $connection_response;
        } catch (\Exception $e) {
            return false;
        }
    }
}
