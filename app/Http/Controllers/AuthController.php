<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Carbon\Carbon;
class AuthController extends Controller
{
    //
    public function sendPhoneOtp(Request $request){
        $validator = Validator::make($request->all(),[
            'phone' => 'required|unique:users|max:15|min:11'
        ]);
        if ($validator->fails()) {
            return response(['status'=> 'error', 'message'=> 'Invalid or duplicate information'], 401);
        }

         if(UserVerification::where('type','phone')->where('data',$request->phone)->where('is_verified',true)->exists())
         {
             return response()->json([
                 'status' => 'failed',
                 'message' => 'Phone number has already been verified'
             ],400);
         }

        $sid = env("TWILIO_ACCOUNT_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $service_id = env('TWILIO_VERIFY_SERVICE_SID');
        $twilio = new Client($sid, $token);
        try {
            $verification = $twilio->verify->v2->services($service_id)->verifications->create($request->phone, "sms");
        return response()->json([
            'status' => 'success',
            'message' => 'Phone verification OTP sent',
        ],200);
    } catch (\Throwable $th) {
            //throw $th;
            return response(['status'=> 'error', 'message'=> 'Phone verification failed.'], 401);
        }
    }

    public function verifyPhoneOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users',
            'code'=> 'required'
        ]);
        if ($validator->fails()) {
            return response(['status'=> 'error', 'message'=> 'Kindly provide a valid code.'], 401);
        }

        if(UserVerification::where('type','phone')->where('data',$request->phone)->where('is_verified',true)->exists())
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'Phone number has already been verified'
            ],400);
        }

        $sid = env("TWILIO_ACCOUNT_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $service_id = env('TWILIO_VERIFY_SERVICE_SID');
        $twilio = new Client($sid, $token);

        $data = array('code' =>$request->code, 'to' =>$request->phone);
        try {
            $verificationCheck = $twilio->verify->v2->services($service_id)->verificationChecks->create($data);
            if($verificationCheck->status == 'approved') {
                return response(['status'=> 'success', 'message'=> 'Phone verification successful.'], 200);
            }
            return response(['status'=> 'error', 'message'=> 'Your phone could not be verified. Invalid Pin'], 401);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['status'=> 'error', 'message'=> 'Your phone could not be verified.'], 401);
        }
    }

    public function getFaculties(Request $request)
    {
        $faculties = Faculty::all();
        return response()->json([
            'status' => 'success',
            'message' => 'All faculties retrieved.',
            'data'=> $faculties
        ],200);
    }

    public function getDepartments(Request $request)
    {
        $departments = Department::all();
        return response()->json([
            'status' => 'success',
            'message' => 'All departments retrieved.',
            'data'=> $departments
        ],200);
    }

    public function getLevels(Request $request, $id)
    {
        if (Department::where('id', $id)->doesntExist()) {
            return response(['status'=> 'error', 'message'=> 'Departments does not exist'], 401);
        }
        $department = Department::where('id', $id)->first();
        $levels = $department->levels()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'All levels retrieved.',
            'data'=> $levels
        ],200);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'phone' => 'required|unique:users|max:15|min:11',
            'gender' => 'required',
            'department' => 'required',
            'level' => 'required',
            'nickname' => 'required',
        ]);
        if ($validator->fails()) {
            return response(['status'=> 'error', 'message'=> 'Invalid or duplicate information'], 401);
        }
            $path = $request->file('image')->store('images', 's3');
            Storage::disk('s3')->setVisibility($path, 'private');

        //Storage::disk('s3')->put($filePath, file_get_contents($file));
//            $image = Image::create([
//                'filename' => basename($path),
                   // user
                  //'type' => 'profile_pic',
//                'url' => Storage::disk('s3')->url($path)
//            ]);

        $user = User::create([
            'phone' => $request->phone,
            'gender' => $request->gender,
            'department' => $request->department,
            'level' => $request->level,
            'nickname' => $request->nickname,
            'photo' => Storage::disk('s3')->url($path),
        ]);





        $phone_verification = UserVerification::updateOrCreate(['data' => $request->phone],[
            'type' => 'phone',
            'data' =>$request->phone,
            'token' => bcrypt(0000),
            'token_expires_at' => Carbon::now()->addMinutes(10),
            'is_verified' => true
        ]);
        $token = $user->createToken('Personal Access Token')->accessToken;
        $data = [
            'status'=> 'success',
            'message'=> 'Registration successful',
            'user'=>$user,
            'token'=>$token
        ];
        return response()->json($data, 200);
    }

    public function sendLoginOtp(Request $request){
        $validator = Validator::make($request->all(),[
            'phone' => 'required|max:15|min:11'
        ]);
        if ($validator->fails()) {
            return response(['status'=> 'error', 'message'=> 'Invalid information'], 401);
        }

        if(
            User::where('phone', $request->phone)->doesntExist() ||
            UserVerification::where('type','phone')->where('data',$request->phone)->doesntExist() ||
            UserVerification::where('type','phone')->where('data',$request->phone)->where('is_verified',false)->exists())
        {
            return response()->json([
                'status' => 'failed',
                'message'=> 'Invalid user or Unverified phone number'
            ],400);
        }

        $sid = env("TWILIO_ACCOUNT_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $service_id = env('TWILIO_VERIFY_SERVICE_SID');
        $twilio = new Client($sid, $token);
        try {
            $verification = $twilio->verify->v2->services($service_id)->verifications->create($request->phone, "sms");
            return response()->json([
                'status' => 'success',
                'message' => 'Login OTP sent',
            ],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['status'=> 'error', 'message'=> 'Login failed.'], 401);
        }
    }

    public function verifyLoginOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'code'=> 'required'
        ]);
        if ($validator->fails()) {
            return response(['status'=> 'error', 'message'=> 'Kindly provide a valid code.'], 401);
        }

        if(
            User::where('phone', $request->phone)->doesntExist() ||
            UserVerification::where('type','phone')->where('data',$request->phone)->doesntExist() ||
            UserVerification::where('type','phone')->where('data',$request->phone)->where('is_verified',false)->exists()
        ){
            return response()->json([
                'status' => 'failed',
                'message'=> 'Invalid user or Unverified phone number'
            ],400);
        }

        $sid = env("TWILIO_ACCOUNT_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $service_id = env('TWILIO_VERIFY_SERVICE_SID');
        $twilio = new Client($sid, $token);
        $data = array('code' =>$request->code, 'to' =>$request->phone);
        try {
            $verificationCheck = $twilio->verify->v2->services($service_id)->verificationChecks->create($data);
            if($verificationCheck->status == 'approved') {
                $user = User::where(['phone'=> $request->phone])->first();
            Auth::login($user, true);
            $session_token = $user->createToken('Personal Access Token')->accessToken;
                return response(['status'=> 'success', 'message'=> 'Login verification successful.', 'user'=>$user, 'token'=>$session_token], 200);
            }
            return response(['status'=> 'error', 'message'=> 'Your login could not be verified - Invalid Pin'], 401);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['status'=> 'error', 'message'=> 'Your login could not be verified.'], 401);
        }
    }

    public function logout() {
        Auth::user()->tokens->each(function ($token){
            $token -> delete();
        });
        return response(['status'=> 'error', 'message'=> 'Login failed.'], 200);
    }



}
