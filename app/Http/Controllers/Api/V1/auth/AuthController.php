<?php

namespace App\Http\Controllers\Api\V1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResources;
use App\Models\User;
use App\Models\Verfication;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Auth;
use JWTAuth;
use TymonJWTAuthExceptionsJWTException;

class AuthController extends Controller
{
    public function unauthrized(Request $request)
    {

        return response()->json(msg($request, not_authoize(), trans('lang.not_authorize')));

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
//           'phone' => 'required|min:12|regex:/(966)[0-9]{8}/',
            'password' => 'required|min:6',
            'device_token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }
        $input = $request->only('phone', 'password');

        if (!$jwt_token = JWTAuth::attempt($input, ['exp' => Carbon::now()->addDays(7)->timestamp])) {
            return response()->json(msg($request, failed(), trans('lang.phoneOrPasswordIncorrect')));
        } else {
            $user = JWTAuth::user();

            if ($user->active == 0) {
                return response()->json(msg($request, failed(), trans('lang.not_active')));
            }
            if ($user->suspend == 1) {
                return response()->json(msg($request, failed(), trans('lang.suspended')));
            }
            $user->fcm_token = $request->device_token;
            $user->save();

            $data = (new UsersResources($user))->token($jwt_token);
            return response()->json(msgdata($request, success(), trans('lang.success'), $data));
        }

    }


    public function SignUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'phone' => 'required|min:12|regex:/(966)[0-9]{8}/',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6|confirmed',
            'device_token' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }

        $data = new User();
        $data->phone = $request->phone;
        $data->password = $request->password;
        $data->save();

        $this->sendCode($data->phone, "activate");

        return response()->json(msg($request, success(), trans('lang.CodeSent')));

    }

    public function ForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'phone' => 'required|min:12|regex:/(966)[0-9]{8}/',
            'phone' => 'required|exists:users',

        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }

        $this->sendCode($request->phone, "reset");

        return response()->json(msg($request, success(), trans('lang.CodeSent')));

    }

    public function check_location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }
        $point = new Point($request->latitude, $request->longitude);
        $zone = Zone::contains('coordinates', $point)->first();
        if (!$zone) {
            $data['in_zone'] = false;
            return response()->json(msgdata($request, success(), trans('lang.out_zone'), $data));
        } else {
            $data['in_zone'] = true;
            return response()->json(msgdata($request, success(), trans('lang.in_zone'), $data));
        }
    }

    public function changePassword(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
            'old_password' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }

        $user = auth()->user();
        if ($request->old_password) {
            $old_password = Hash::check($request->old_password, $user->password);
            if ($old_password != true) {
                return response()->json(msg($request, failed(), trans('lang.old_passwordError')));

            }
        }
        $user->password = $request->password;
        $user->save();
        $token = $request->bearerToken();

        $data = (new UsersResources($user))->token($token);
        return response()->json(msgdata($request, success(), trans('lang.passwordChangedSuccess'), $data));


    }

    public function UpdateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            'gender' => 'required|in:male,female',
            'age' => 'required',
            'weight' => 'required',
            'height' => 'required',
            'phone' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }

        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->image = $request->image;
        $user->gender = $request->gender;
        $user->age = $request->age;
        $user->weight = $request->weight;
        $user->height = $request->height;
        $user->phone = $request->phone;
        $user->save();
        $token = $request->bearerToken();

        $data = (new UsersResources($user))->token($token);
        return response()->json(msgdata($request, success(), trans('lang.success'), $data));


    }


    public function Verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'phone' => 'required|min:12|regex:/(966)[0-9]{8}/',
            'phone' => 'required|exists:users,phone',
            'code' => 'required|min:4',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }

        $user = User::where('phone', $request->phone)->first();

//        $type = $user->active == 0 ? "activate" : "reset";

        $verfication = Verfication::where('phone', $request->phone)
            ->where('code', $request->code)
//            ->where('type', $type)
            ->first();


        if ($verfication) {
            if (!$verfication->expired_at > Carbon::now()->toDateTimeString()) {
                return response()->json(msg($request, failed(), trans('lang.codeExpired')));
            }
//            if ($type == "activate") {
            $user->active = 1;
            $user->save();
            $jwt_token = JWTAuth::fromUser($user);
            $data = (new UsersResources($user))->token($jwt_token);
            return response()->json(msgdata($request, success(), trans('lang.Verified_success'), $data));
//            } else {
//                $jwt_token = JWTAuth::fromUser($user);
//                $data = (new UsersResources($user))->token($jwt_token);
//                return response()->json(msgdata($request, success(), trans('lang.Verified_success'), $data));
//            }
        } else {
            return response()->json(msg($request, failed(), trans('lang.codeError')));
        }


    }

    public
    function sendCode($phone, $type)
    {

        $code = rand(0000, 9999);
        $code = 1111;

        Verfication::updateOrcreate
        (
            [
                'phone' => $phone,
            ],
            [
                'code' => $code,
                'type' => $type,
                'expired_at' => Carbon::now()->addHour()->toDateTimeString()
            ]
        );

        return true;
    }

    public
    function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'phone' => 'required|min:12|regex:/(966)[0-9]{8}/',
            'phone' => 'required|exists:users,phone',

        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }
        $user = User::where('phone', $request->phone)->first();

        $type = $user->active == 0 ? "activate" : "reset";

        $this->sendCode($request->phone, $type);

//        $jwt_token = JWTAuth::fromUser($user);
//        $data = (new UsersResources($user))->token($jwt_token);
        return response()->json(msg($request, success(), trans('lang.success')));

    }


    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_type' => 'required|in:facebook,google',
            'social_id' => 'required',
//            'phone' => 'required',
        ]);
        if (!is_array($validator) && $validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }
//        // 1- check phone exists
//        $user = User::where('phone', $request->phone)->first();
//        if ($user) {
//            if ($request->social_type == 'facebook') {
//                $user->social_id = $request->social_id;
//            } else {
//                $user->social_id = $request->social_id;
//            }
//            if (empty($user->email_verified_at)) {
//                $user->email_verified_at = Carbon::now();
//            }
//            $user->phone = $request->phone;
//            $user->fcm_token = $request->device_token;
//            $user->save();
//            $jwt_token = JWTAuth::fromUser($user);
//            $data = (new UsersResources($user))->token($jwt_token);
//            return response()->json(msgdata($request, success(), trans('lang.success'), $data));
//        }

        // 2- check social id exists

        $userFound = User::where('social_id', $request->social_id)
            ->where('provider', $request->social_type)
            ->first();
        if ($userFound) {
//            $userFound->phone = $request->phone;
            $userFound->fcm_token = $request->device_token;
            $userFound->save();
            $jwt_token = JWTAuth::fromUser($userFound);
            $data = (new UsersResources($userFound))->token($jwt_token);
            return response()->json(msgdata($request, success(), trans('lang.success'), $data));
        }

        // 3- if not login with social before
        try {


            if ($request->social_type == 'facebook') { // facebook
                $user = User::create([
                    'social_id' => $request->social_id,
                    'fcm_token' => $request->device_token,
//                    'phone' => $request->phone,
                    'email_verified_at' => Carbon::now(),
                    'active' => 1,
                    'provider' => 'facebook'
                ]);
            } else {
                // google
                $user = User::create([
                    'social_id' => $request->social_id,
                    'fcm_token' => $request->device_token,
//                    'phone' => $request->phone,
                    'email_verified_at' => Carbon::now(),
                    'active' => 1,
                    'provider' => 'google'
                ]);
            }
        }catch (\Exception $e){
            return response()->json(msg($request, failed(), trans('lang.PhoneExists')));
        }

        $jwt_token = JWTAuth::fromUser($user);
        $data = (new UsersResources($user))->token($jwt_token);
        return response()->json(msgdata($request, success(), trans('lang.success'), $data));
    }
}
