<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pitch;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use JWTAuth;
use TymonJWTAuthExceptionsJWTException;

class HomeController extends Controller
{

    public function bookOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pitche_id' => 'required|exists:pitches,id',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => failed(), 'msg' => $validator->messages()->first()]);
        }

        //check if date is valid for booking
        if ($request->date < Carbon::now()) {
            return response()->json(msg($request, failed(), trans('lang.invalid_date')));

        }

        //check if slot is booked before
        $orderCheck = $this->orderCheck($request->pitche_id,$request->date,$request->time);
        if ($orderCheck) {
            return response()->json(msg($request, failed(), trans('lang.booked_before')));

        }

        //        $user_id = auth()->user()->id;
        $user_id = 1;
        $pitche = Pitch::whereId($request->pitche_id)->select('stadium_id','slot')->first();
        $order = Order::create([
            'user_id' => $user_id,
            'stadium_id' => $pitche->stadium_id,
            'pitche_id' => $request->pitche_id,
            'date' => $request->date,
            'start' => $request->time,
            'end' => Carbon::parse($request->time)->addMinutes($pitche->slot),
        ]);

        return response()->json(msgdata($request, success(), trans('lang.success'), $order));
    }

    public function getPitchSlotsByStadiumId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'msg' => $validator->messages()->first()]);
        }
        $pitches = Pitch::get();
        foreach ($pitches as $pitche){
            $orders = Order::where('date',$request->date)
                ->where('pitche_id',$pitche->id)
                ->pluck('start')->toArray();
            $times =[];
            $intervals = CarbonInterval::minutes($pitche->slot)->toPeriod($pitche->start, $pitche->end);
            foreach ($intervals as $date) {
                array_push($times,$date->format('H:i'));
            }
            $result=array_diff($times,$orders);
            $pitche->intervals = $result;
        }
        return response()->json(msgdata($request, success(), trans('lang.success'), $pitches));
    }

    public function orderCheck($pitche_id,$date,$time)
    {
        return Order::where('pitche_id',$pitche_id)
            ->where('date',$date)
            ->where('start',$time)
            ->first();
    }


}
