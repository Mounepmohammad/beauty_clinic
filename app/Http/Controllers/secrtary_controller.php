<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\secrtary;
use App\Models\Appointment;
use App\Models\service;
use App\Models\doctor;
use Validator;


class secrtary_controller extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // public function __construct() {
    //     // $this->middleware('doctor.auth', ['except' => ['doctor_login','doctor_register']]);
    //     $this->middleware('doctor.auth:doctor_api')->except(['doctor_register']);
    // }

    public function __construct() {
        $this->middleware('secrtary.auth', ['except' => [
            'secrtary_login', 'secrtary_register'
        ]]);
        }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function secrtary_login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth('secrtary_api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //$token = auth('api')->attempt($validator->validated());

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function secrtary_register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:secrtaries',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $secrtary = secrtary::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'secrtary successfully registered',
            'secrtary' => $secrtary
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function secrtary_logout() {
        auth('secrtary_api')->logout();

        return response()->json(['message' => 'secrtary successfully signed out'],201);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function secrtary_Profile() {
        return response()->json([auth('secrtary_api')->user()],201);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('secrtary_api')->factory()->getTTL() * 60,
            'secrtary' => auth('secrtary_api')->user()
        ],201);
    }





    public function doctors()
    {
        $doctors = doctor::all();
        return response()->json(['doctors'=> $doctors],201);
    }



    public function services()
    {
        $services = service::all();
        return response()->json(['services'=> $services],201);
    }

    public function doctor_appointments(Request $request){


        $validator = Validator::make($request->all(), [
            'id'=> 'required',
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $appointments = Appointment::where('doctor_id', $request->id)->where('date', $request->date)
                                   ->where(function($query) {
                                       $query->where('state', 'processing')
                                             ->orWhere('state', 'accept');
                                   })
                                   ->get();
        return response()->json([
            'appointments'=>$appointments
        ],201);
    }


    public function reserve_appointment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'id'=> 'required',
            'name'=>'required',
            'date' => 'required|date|date_format:Y-m-d',
            'fromtime' => 'required|date_format:H:i',
            'totime' => 'required|date_format:H:i|after:fromtime',
            'servicename' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $fromTime = $request->fromtime;
        $toTime = $request->totime;

        $appointments = Appointment::where('doctor_id', $request->id)->where('date', $request->date)
        ->where(function($query) {
            $query->where('state', 'processing')
                  ->orWhere('state', 'accept');
        })
        ->get();

        foreach ($appointments as $slot) {
            $time = strtotime($slot->totime);
            $time2= $time - (1*60);
            $totime2 = date("H:i:s", $time2);
            if (
                ($fromTime >= $slot->fromtime && $fromTime < $slot->totime2) ||
                ($toTime > $slot->fromtime && $toTime <= $slot->totime) ||
                ($fromTime <= $slot->fromtime && $toTime >= $slot->totime)
            ) {
                return response()->json(['message' => 'The time slot overlaps with an existing slot.'], 422);
            }
        }

        $appointment = Appointment::create(array_merge(
            $validator->validated(),
           [

            'user_id' => '0',
            'doctor_id' => $request->id,
            'state'=>'accept'

            ]

        ));
        return response()->json([

            'message' => 'appointment successfully reserve ',
            'appointment' => $appointment

        ],201);

    }

    public function update_appointment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'id'=> 'required',
            'name'=>'sometimes|required',
            'date' => 'sometimes|required|date|date_format:Y-m-d',
            'fromtime' => 'sometimes|required|date_format:H:i',
            'totime' => 'sometimes|required|date_format:H:i|after:fromtime',
            'servicename' => 'sometimes|required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $appoint = Appointment::find($request->id);
        $doctor_id = $appoint->doctor_id;

        $fromTime = $request->fromtime;
        $toTime = $request->totime;

        $appointments = Appointment::where('doctor_id', $doctor_id)->where('date', $request->date)
        ->where(function($query) {
            $query->where('state', 'processing')
                  ->orWhere('state', 'accept');
        })
        ->get();

        foreach ($appointments as $slot) {
            $time = strtotime($slot->totime);
            $time2= $time - (1*60);
            $totime2 = date("H:i:s", $time2);
            if (($slot->id != $request->id) && (
                ($fromTime >= $slot->fromtime && $fromTime < $slot->totime2) ||
                ($toTime > $slot->fromtime && $toTime <= $slot->totime) ||
                ($fromTime <= $slot->fromtime && $toTime >= $slot->totime)
            )) {
                return response()->json(['message' => 'The time appointment overlaps with an existing appointment.'], 422);
            }
        }

        $appoint->update(array_merge(
            $validator->validated(),

        ));
        return response()->json([

            'message' => 'appointment successfully updated ',
            'appointment' => $appoint

        ],201);

    }


    public function delete_appointment(Request $request)
    {
        $appointment = Appointment::find($request->id);

        if (!$appointment) {
            return response()->json(['message' => 'appointment not found'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'appointment deleted successfully']);
    }

}

