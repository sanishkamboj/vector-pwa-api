<?php

namespace App\Http\Controllers;

use App\SiteAttribute;
use App\SiteSubType;
use App\SiteType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\TreatmentProduct;
use App\TaskLandingRate;
use App\TaskLarval;
use App\TaskTrap;
use App\TaskTreatment;
use App\TaskOther;
use App\TrapType;
use App\TaskType;
use App\Species;
use App\SrDetail;
use App\UserMas;
use App\City;
use App\Site;
use App\SiteAttrData;
use App\Zone;
use View;
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
            $data['site_attr_data'] = SiteAttrData::select('iSiteId as siteid', 'iSAttributeId as site_attr')->get();
            $data['cities'] = City::select('iCityId as id', 'vCity as name')->get();
            $data['species'] = Species::where('iStatus', 1)->get();
            $data['products'] = TreatmentProduct::where('iStatus', 1)->get();
            $data['trap_type'] = TrapType::where('iStatus', 1)->get();
            $data['task_type'] = TaskType::where('iStatus', 1)->get();
            $data['sr_details'] = SrDetail::get();
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
        $country = $request->country;
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
                $site_type = $this->getSiteTypeIcon($site->stypeid, $country);
                $address = '';
                if($site->vAddress1 != ''){
                    $address .= $site->vAddress1;
                }
                if($site->vAddress2 != ''){
                    $address .= ", ".$site->vAddress2;
                }
                if($site->vStreet != ''){
                    $address .= ", ".$site->vStreet;
                }
                $address .= $this->getAddress($site->siteid);
                $geoArr['sites'][$i]['address'] = $address;
                $geoArr['sites'][$i]['icon'] = $site_type['icon'];
                $geoArr['sites'][$i]['site_type_name'] = $site_type['name'];
                $geoArr['sites'][$i]['site_attr_name'] = $this->getSiteAttr($site->siteid);
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
            } else if(isset($site->point) && $site->point != ''){

                $point = str_replace("POINT(", '', $site->point);
                $point = str_replace(")", '', $point);

                $pointArr = explode(" ", $point);

                    //print_r($latLngArr);

                $geoArr['sites'][$i]['point'][] =  array(
                    'lat' => (float) $pointArr[1],
                    'lng' => (float) $pointArr[0]
                    );
                    $address = '';
                if($site->vAddress1 != ''){
                    $address .= $site->vAddress1;
                }
                if($site->vAddress2 != ''){
                    $address .= ", ".$site->vAddress2;
                }
                if($site->vStreet != ''){
                    $address .= ", ".$site->vStreet;
                }
                $address .= $this->getAddress($site->siteid);
                $geoArr['sites'][$i]['address'] = $address;
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $site_type = $this->getSiteTypeIcon($site->stypeid, $country);
                $geoArr['sites'][$i]['icon'] = $site_type['icon'];
                $geoArr['sites'][$i]['site_type_name'] = $site_type['name'];
                $geoArr['sites'][$i]['site_attr_name'] = $this->getSiteAttr($site->siteid);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
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
                   
                }
                $address = '';
                if($site->vAddress1 != ''){
                    $address .= $site->vAddress1;
                }
                if($site->vAddress2 != ''){
                    $address .= ", ".$site->vAddress2;
                }
                if($site->vStreet != ''){
                    $address .= ", ".$site->vStreet;
                }
                $address .= $this->getAddress($site->siteid);
                $geoArr['sites'][$i]['address'] = $address;
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $site_type = $this->getSiteTypeIcon($site->stypeid, $country);
                $geoArr['sites'][$i]['icon'] = $site_type['icon'];
                $geoArr['sites'][$i]['site_type_name'] = $site_type['name'];
                $geoArr['sites'][$i]['site_attr_name'] = $this->getSiteAttr($site->siteid);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
            }
            $i++;
        }
        //$data['sites'] = $geoArr;
        $response = [
            'status' => 200,
            'message' => 'Data found',
            'data' => $geoArr
        ];

        return response()->json($response);

    }

    public function getSiteTypeIcon($siteId, $country){
        
        $data = SiteType::select("vTypeName", 'icon')->where(['iSTypeId' => $siteId, 'iStatus' => 1])->first();
        if(is_null($data)){
            $vIcon = "https://".$country.".vectorcontrolsystem.com/images/black_icon.png";
        } else {
            if($data->icon != '') {
                $vIcon = "https://".$country.".vectorcontrolsystem.com/storage/site_type_icon/".$data->icon;
            }
        }
        $siteType['name'] = $data->vTypeName;
        $siteType['icon'] = $vIcon;
        return $siteType;
    }
    public function getSiteAttr($siteId){
        $siteAttr = SiteAttrData::with('attribute')->where('iSiteId', 1)->get();
        $attrs = [];
        foreach($siteAttr as $attr){
            $attrs[] = $attr->attribute->vAttribute;
        }
        return implode(',', $attrs);
    }

    public function getAddress($id){
        $data = Site::with('city', 'state', 'country')->where('iSiteId', $id)->first();
        return $data->city->vCity.", ".$data->state->vState.", ".$data->country->vCounty;
    }
    public function getZones(Request $request){
        $zones = Zone::getData();
        //dd($zones);
        $i=0;
        $geoArr = array();
        if(!empty($zones)){
            foreach($zones as $key => $zone){
                $polygon = str_replace("POLYGON((", '', $zone->geotxt);
                $polygon = str_replace("))", '', $polygon);

                    //print_r($polygon);

                $polyArr = explode(",", $polygon);

                    //print_r($polyArr);
                $geoArr['polyZone'][$i]['zoneid'] = $zone->iZoneId;
                $geoArr['polyZone'][$i]['name'] = $zone->vZoneName;
                foreach($polyArr as $latlng){
                    $latLngArr = explode(" ", $latlng);

                        //print_r($latLngArr);
                    $geoArr['polyZone'][$i]['lat_long'][] = array(
                        'lat' => (float) $latLngArr[1],
                        'lng' => (float) $latLngArr[0]
                        );
                    
                }

                $i++;
            }
        }
        $response = [
            'status' => 200,
            'message' => 'Data found',
            'data' => $geoArr
        ];

        return response()->json($response);
        
    }

    public function getSiteByAddress(Request $request){
        $postData = $request->all();
        $country = $request->country;
        $sites = Site::getSitesByAddress($postData);
        $geoArr = array();
        $i = 0;
        if(count($sites) == 0){
            $response = [
                'status' => 404,
                'message' => 'No Data found',
                'data' => []
            ];
    
            return response()->json($response);
        }
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
                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
            } else if(isset($site->point) && $site->point != ''){

                $point = str_replace("POINT(", '', $site->point);
                $point = str_replace(")", '', $point);

                $pointArr = explode(" ", $point);

                    //print_r($latLngArr);

                $geoArr['sites'][$i]['point'][] =  array(
                    'lat' => (float) $pointArr[1],
                    'lng' => (float) $pointArr[0]
                    );
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
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
                   
                }
                $geoArr['sites'][$i]['siteid'] = $site->siteid;
                $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                $geoArr['sites'][$i]['site_name'] = $site->site_name;
                //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
            }
            $i++;
            
        }
        $response = [
            'status' => 200,
            'message' => 'Data found',
            'data' => $geoArr
        ];

        return response()->json($response);
    }

    public function getSR(Request $request, $id){
        $country = $request->country;
        $srSite = SrDetail::getSRData($id);
        //dd($srSite);
        $i = 0;
        foreach($srSite as $site){
           
            if(isset($site->vLatitude) && $site->vLongitude != ''){
                $vLatitude = $site->vLatitude;
                $vLongitude = $site->vLongitude;
                $srData[$i]['point'][] =  array(
                    'lat' => (float) $vLatitude,
                    'lng' => (float) $vLongitude
                    );

                $srData[$i]['icon'] = "https://".$country.".vectorcontrolsystem.com/images/black_icon.png";

                $vFirstName = (isset($site->vFirstName) ? $site->vFirstName : '');
                $vLastName = (isset($site->vLastName) ? $site->vLastName : '');
                $vAddress1 = (isset($site->vAddress1) ? $site->vAddress1 : '');
                $vStreet = (isset($site->vStreet) ? $site->vStreet : '');
                $vCity = (isset($site->vCity) ? $site->vCity : '');
                $vState = (isset($site->vState) ? $site->vState : '');

                $vRequestType = '';
                if($site->bMosquitoService == 't' && $site->bCarcassService != 't') {
                    $vRequestType = 'Mosquito Inspection/Treatment';
                }else if($site->bMosquitoService != 't' && $site->bCarcassService == 't') {
                    $vRequestType = 'Carcass Removal';
                }else if($site->bMosquitoService == 't' && $site->bCarcassService == 't') {
                    $vRequestType = 'Mosquito Inspection/Treatment | Carcass Removal';
                }

                $vStatus = '';
                if($site->iStatus == 1){
                    $vStatus = 'Draft';
                    $srData[$i]['icon'] = "https://".$country.".vectorcontrolsystem.com/images/sr_red.png";
                }else if($site->iStatus == 2){
                    $vStatus = 'Assigned';
                    $srData[$i]['icon'] = "https://".$country.".vectorcontrolsystem.com/images/sr_yellow.png";
                }else if($site->iStatus == 3){
                    $vStatus = 'Review';
                    $srData[$i]['icon'] = "https://".$country.".vectorcontrolsystem.com/images/sr_green.png";
                }else if($site->Status == 4){
                    $vStatus = 'Complete';
                    $srData[$i]['icon'] = "https://".$country.".vectorcontrolsystem.com/images/sr_black.png";
                }
                
                $vAssignTo = ($site->vAssignTo ? $site->vAssignTo :'');
                $srData[$i]['vName'] = $vFirstName. ' '.$vLastName;
                $srData[$i]['vAddress'] = $vAddress1.' '.$vStreet.' '.$vCity.' '.$vState ;
                $srData[$i]['vRequestType'] = $vRequestType;
                $srData[$i]['vStatus'] = $vStatus;
                $srData[$i]['vAssignTo'] = $vAssignTo;
                $view = View::make('sr-window', ['id' => $site->iSRId, 'vRequestType' => $vRequestType, 'address' => $srData[$i]['vAddress'], 'assignto' => $vAssignTo, 'status' => $vStatus, 'country' => $country, 'name' => $srData[$i]['vName']]);
                $srData[$i]['infowindow'] = $view->render();


            }
        }
        
        if(!is_null($srData)){
            $response = [
                'status' => 200,
                'message' => 'Data found',
                'data' => $srData,

            ];
    
            return response()->json($response);
        }
        $response = [
            'status' => 404,
            'message' => 'No Data found',
            'data' => []
        ];

        return response()->json($response);
    }

    public function getSiteByID(Request $request, $id){
        $country = $request->country;
        $sites = Site::getSiteById($id);
        $geoArr = [];
        $message = 'No Data Found';
        $status = '404';
        $i = 0;
        if(count($sites)){
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
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
                } else if(isset($site->point) && $site->point != ''){
    
                    $point = str_replace("POINT(", '', $site->point);
                    $point = str_replace(")", '', $point);
    
                    $pointArr = explode(" ", $point);
    
                        //print_r($latLngArr);
    
                    $geoArr['sites'][$i]['point'][] =  array(
                        'lat' => (float) $pointArr[1],
                        'lng' => (float) $pointArr[0]
                        );
                    $geoArr['sites'][$i]['siteid'] = $site->siteid;
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
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
                       
                    }
                    $geoArr['sites'][$i]['siteid'] = $site->siteid;
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
                }
                $i++;
                $message = "Data Found";
                $status = '200';
            }
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $geoArr
        ];

        return response()->json($response);
    }

    public function UploadData(Request $request){
        $postData = $request->all();
        $sites = $postData['sites'];
        $taskLandigRate = $postData['taskLandingRate'];
        $arr = [];
        foreach($sites as $k => $site){
            $arr['vName'] = $site['name'];
            $arr['iSTypeId'] = $site['siteTypeId'];
            $arr['iSSTypeId'] = $site['siteSubTypeId'];
            $arr['vAddress1'] = $site['address1'];
            $arr['iGeometryType'] = $site['geometryType'];
            if(isset($site['polygonLatLong']) && $site['polygonLatLong'] != ''){
                $latLngArr = json_decode($site['polygonLatLong']);
                $lnlg = [];  
                if(count($latLngArr) == 1){
                    $latLngArr = $latLngArr[0];
                }
                foreach($latLngArr as $lat_lng){
                        if($lat_lng->lng != ''){
                            $lnlg[] = $lat_lng->lng." ".$lat_lng->lat;
                        }
                    }
                    $lnlg[] = $lnlg[0];
                $arr['vPolygonLatLong'] = implode(",", $lnlg);
            } else {
                $arr['vPolygonLatLong'] = 'NULL';
            }
            if(isset($site['polylineLatLong']) && $site['polylineLatLong'] != ''){
                $latLngArr = json_decode($site['polylineLatLong']);
                $lnlg = [];
                foreach($latLngArr as $lat_lng){
                    if($lat_lng->lng != ''){
                        $lnlg[] = $lat_lng->lng." ".$lat_lng->lat;
                    }
                }
                
                $arr['vPolyLineLatLong'] = implode(",", $lnlg);
            } else {
                $arr['vPolyLineLatLong'] = 'NULL';
            }
            $arr['vLatitude'] = $site['latitude'];
            $arr['vLongitude'] = $site['longitude'];
            $arr['iStatus'] = $site['status'];
            $arr['dAddedDate'] = date("Y-m-d h:i:s"); 
            Site::addRecord($arr);       
        }
       
        return response()->json($taskLandigRate);
    }

    function downloadData(Request $request){
        $country = $request->country;
        $date = date("Y-m-d", strtotime($request->date));
        $data = [];
        $sites = Site::getSitesByDate($date);
        $data['sites'] = $this->siteArr($sites, $country);
        $data['site_types'] = SiteType::select('iSTypeId as id' , 'vTypeName as name', 'iStatus as status', 'icon as icon')->where('iStatus', 1)->get();
        $data['site_sub_types'] = SiteSubType::select('iSSTypeId as id', 'iSTypeId as site_type_id', 'vSubTypeName as name', 'iStatus as status')->where('iStatus', 1)->get();
        $data['site_attributes'] = SiteAttribute::select('iSAttributeId as id', 'vAttribute as name', 'iStatus as status')->where('iStatus', 1)->get();
        $data['site_attr_data'] = SiteAttrData::select('iSiteId as siteid', 'iSAttributeId as site_attr')->get();
        //$data['cities'] = City::select('iCityId as id', 'vCity as name')->get();
        $data['species'] = Species::where('iStatus', 1)->get();
        $data['products'] = TreatmentProduct::where('iStatus', 1)->get();
        $data['trap_type'] = TrapType::where('iStatus', 1)->get();
        $data['task_type'] = TaskType::where('iStatus', 1)->get();
        $data['sr_details'] = SrDetail::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();
        $data['taskLandingRate'] = TaskLandingRate::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();
        $data['taskLarval'] = TaskLarval::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();
        $data['taskTrap'] = TaskTrap::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();
        $data['taskTreatment'] = TaskTreatment::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();
        $data['taskOther'] = TaskOther::where('dAddedDate', '>=',$date)->orWhere('dModifiedDate', '>=', $date)->get();

        $response = [
            'status' => 200,
            'message' => 'Data found',
            'data' => $data
        ];

        return response()->json($response);

    }
    public function siteArr($sites, $country){
        $geoArr = [];
        $message = 'No Data Found';
        $status = '404';
        $i = 0;
        if(count($sites)){
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
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
                } else if(isset($site->point) && $site->point != ''){
    
                    $point = str_replace("POINT(", '', $site->point);
                    $point = str_replace(")", '', $point);
    
                    $pointArr = explode(" ", $point);
    
                        //print_r($latLngArr);
    
                    $geoArr['sites'][$i]['point'][] =  array(
                        'lat' => (float) $pointArr[1],
                        'lng' => (float) $pointArr[0]
                        );
                    $geoArr['sites'][$i]['siteid'] = $site->siteid;
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
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
                       
                    }
                    $geoArr['sites'][$i]['siteid'] = $site->siteid;
                    $geoArr['sites'][$i]['icon'] = $this->getSiteTypeIcon($site->stypeid, $country);
                    $geoArr['sites'][$i]['cityid'] = $site->iCityId;
                    $geoArr['sites'][$i]['zoneid'] = $site->iZoneId;
                    $geoArr['sites'][$i]['stypeid'] = $site->stypeid;
                    $geoArr['sites'][$i]['site_name'] = $site->site_name;
                    //$geoArr['sites'][$i]['site_attr'] = $site->site_attr;
                }
                $i++;
                $message = "Data Found";
                $status = '200';
            }
        }
        return $geoArr;
    }

}

