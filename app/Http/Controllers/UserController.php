<?php

namespace App\Http\Controllers;

use App\SiteAttribute;
use App\SiteSubType;
use App\SiteType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\UserMas;
use App\City;
use App\Site;

class UserController extends Controller
{
    public function check_user(Request $request){

        $postData = $request->all();
        $db_name = $postData['country']."_vectorerp_production";
        //$this->switchDatabase($db_name, 'mysql');
        $user = UserMas::where('vUsername', $postData['email'])->first();
        if($user){
            $password = $postData['password'];
            $userPassword = $this->decrypt_password($user);
            if($password === $userPassword){
                $response = [
                    'status' => 200,
                    'message' => 'Welcome back again',
                    'token' => Str::random(60),
                    'data' => $user
                ];
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Please enter correct details',
                    'data' => []
                ];
            }
        } else {
            $response = [
                'status' => 404,
                'message' => 'User not found',
                'data' => []
            ];
        }
        return response()->json($response);

        
    }

    public function decrypt_password($user) {
        // print_r($result);exit();    
         define('AES_256_CBC', 'aes-256-cbc');
         $vPassword = $user->vPassword;
         //random number for descrypt(salt)
     
         $salt = $user->sSalt;
     
         $iv = $salt; //cipher length.
         $decrypted = openssl_decrypt($vPassword, AES_256_CBC, $salt, 0, $iv);
         return $decrypted;
    }

    public function get_table_data(Request $request){
        try{
            $data['site_types'] = SiteType::select('iSTypeId as id' , 'vTypeName as name', 'iStatus as status', 'icon as icon')->where('iStatus', 1)->get();
            $data['site_sub_types'] = SiteSubType::select('iSSTypeId as id', 'iSTypeId as site_type_id', 'vSubTypeName as name', 'iStatus as status')->where('iStatus', 1)->get();
            $data['site_attributes'] = SiteAttribute::select('iSAttributeId as id', 'vAttribute as name', 'iStatus as status')->where('iStatus', 1)->get();
            $data['cities'] = City::select('iCityId as id', 'vCity as name')->get();
            
            $response = [
                'status' => 200,
                'message' => 'Data found',
                'data' => $data
            ];
            return response()->json($response);
        } catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    public function get_sites_data(Request $request){
        $page = $request->page;
        $offset = ($page * 100);
        $sites = Site::getSiteData($offset);
       
        $geoArr = array();
        $i = 0;
        foreach($sites as $site){
            //dd($site);
            if(isset($site->polygon) && $site->polygon != ''){
                    //print_r($site);

                $polygon = str_replace("POLYGON((", '', $site->polygon);
                $polygon = str_replace("))", '', $polygon);

                    //print_r($polygon);

                $polyArr = explode(",", $polygon);

                    //print_r($polyArr);

                foreach($polyArr as $latlng){
                    $latLngArr = explode(" ", $latlng);

                        //print_r($latLngArr);
                    $geoArr['sites'][$i]['polygon'][] = array(
                        'lat' => (float) $latLngArr[1],
                        'lng' => (float) $latLngArr[0]
                        );
                    $i++;
                }
                if(isset($site->polycenter) && $site->polycenter != ''){
                    $center = str_replace("POINT(", '', $site->polycenter);
                    $polyCenter = str_replace(")", '', $center);
                    $polyCenterArr = explode(" ", $polyCenter);
                        //print_r($polyCenterArr); die;
                    $geoArr['sites'][$i]['polyCenter'] = array(
                        'lat' => (float) $polyCenterArr[1],
                        'lng' => (float) $polyCenterArr[0]
                        );
                }
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
            } else if(isset($site->point) && $site->point != ''){

                $point = str_replace("POINT(", '', $site->point);
                $point = str_replace(")", '', $point);

                $pointArr = explode(" ", $point);

                    //print_r($latLngArr);

                $geoArr['sites'][$i]['point'][] =  array(
                    'lat' => (float) $pointArr[1],
                    'lng' => (float) $pointArr[0]
                    );
                $i++;
                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
            } else if(isset($site->poly_line) && $site->poly_line != ''){
                $polyLine = str_replace("LINESTRING(", '', $site->poly_line);
                $polyLine = str_replace(")", '', $polyLine);

                    //print_r($polygon);

                $polyLineArr = explode(",", $polyLine);

                    //print_r($polyArr);

                foreach($polyLineArr as $latlng){
                    $polyLineLatLngArr = explode(" ", $latlng);

                        //print_r($latLngArr);
                    $geoArr['sites'][$i]['poly_line'][] = array(
                        'lat' => (float) $polyLineLatLngArr[1],
                        'lng' => (float) $polyLineLatLngArr[0]
                        );
                    $i++;
                }

                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
            }

        }
        //$data['sites'] = $geoArr;
        $response = [
            'status' => 200,
            'message' => 'Data found',
            'data' => $geoArr
        ];

        return response()->json($response);

    }

    public function getSiteTypeIcon($siteId){
        
        $data = SiteType::select('icon')->where(['iSTypeId' => $siteId, 'iStatus' => 1])->first();
        if(is_null($data)){
            $vIcon = "images/black_icon.png";
        } else {
            if($data->icon != '') {
                $vIcon = $data->icon;
            }
        }
        return $vIcon;
    }
}
