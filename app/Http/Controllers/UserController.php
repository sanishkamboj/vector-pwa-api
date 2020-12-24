<?php

namespace App\Http\Controllers;

use App\SiteAttribute;
use App\SiteSubType;
use App\SiteType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\UserMas;
use App\City;



class UserController extends Controller
{
    public function check_user(Request $request){
        $postData = $request->all();
        $country = $postData['country'];
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
            return response()->json($e);
        }
    }
}
