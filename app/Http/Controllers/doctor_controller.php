<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\doctor;
use App\Models\Appointment;
use App\Models\patient;
use App\Models\record;
use Validator;


class doctor_controller extends Controller
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
        $this->middleware('doctor.auth', ['except' => [
            'doctor_login', 'doctor_register'
        ]]);
        }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctor_login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth('doctor_api')->attempt($validator->validated())) {
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
    public function doctor_register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:doctors',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $doctor = doctor::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'doctor successfully registered',
            'doctor' => $doctor
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctor_logout() {
        auth('doctor_api')->logout();

        return response()->json(['message' => 'doctor successfully signed out'],201);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctor_Profile() {
        return response()->json(auth('doctor_api')->user());
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
            'expires_in' => auth('doctor_api')->factory()->getTTL() * 60,
            'doctor' => auth('doctor_api')->user()
        ]);
    }

    public function add_patient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'service_name' => 'required|string|between:2,100',
            'location' => 'nullable|string|between:2,100',
            'phone' => 'nullable|numeric|digits:10',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $patient =patient::create(array_merge(
            $validator->validated(),
            ['doctor_id' =>auth('doctor_api')->user()->id,]

        ));
        return response()->json([
            'message' => 'patient successfully added',
            'patient' => $patient
        ], 201);
    }



    public function update_patient(Request $request){

        $validator = Validator::make($request->all(), [
            'id'=> 'required',
            'name' => 'sometimes|required|string|between:2,100',
            'service_name' => 'sometimes|required|string|between:2,100',
            'location' => 'sometimes|nullable|string|between:2,100',
            'phone' => 'sometimes|nullable|numeric|digits:10',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $patient = patient::find($request->id);

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }



        $patient->update(array_merge(
            $validator->validated(),
            ));
        return response()->json([
            'message' => 'patient successfully updated',
            'patient' => $patient
        ], 201);
    }

    public function delete_patient(Request $request)
    {
        $patient = patient::find($request->id);

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }

        $patient->delete();
        return response()->json(['message' => 'patient deleted successfully']);
    }




    public function add_record(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id'=>'required',
            'date' => 'required|date|date_format:Y-m-d',
            'description' => 'required|string',
            'note' => 'nullable|string',

        ]);
        $patient = patient::find($request->patient_id);

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $record =record::create(array_merge(
            $validator->validated(),
        ));
        return response()->json([
            'message' => 'record successfully added',
            'record' => $record
        ], 201);
    }


    public function update_record(Request $request)

    {

        $record = record::find($request->id);

        if (!$record) {
            return response()->json(['message' => 'record not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date|date_format:Y-m-d',
            'description' => 'sometimes|required|string',
            'note' => 'sometimes|nullable|string',


        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $record->update(array_merge(
            $validator->validated(),
            ));
        return response()->json([
            'message' => 'record successfully updated',
            'record' => $record
        ], 201);
    }

    public function delete_record(Request $request)
    {
        $record = record::find($request->id);

        if (!$record) {
            return response()->json(['message' => 'record not found'], 404);
        }

        $record->delete();
        return response()->json(['message' => 'record deleted successfully']);
    }

    public function my_patients(Request $request){

        $patients = patient::where('doctor_id', auth('doctor_api')->user()->id)->get();

        return response()->json([
            'patients'=>$patients
            ],201);
}

     public function patient_record(Request $request){

        $patient = patient::find($request->id);

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }
        return response()->json([
            'records'=>$patient->records()->get(),
            ],201);
      }



    public function my_appointments(Request $request){


        $validator = Validator::make($request->all(), [
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $appointments = Appointment::where('doctor_id', auth('doctor_api')->user()->id)->where('date', $request->date)
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
            'date' => 'required|date|date_format:Y-m-d',
            'fromtime' => 'required|date_format:H:i',
            'totime' => 'required|date_format:H:i|after:fromtime',
            'servicename' => 'required|string',
        ]);

        $patient = patient::find($request->id);

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $fromTime = $request->fromtime;
        $toTime = $request->totime;

        $appointments = Appointment::where('doctor_id', auth('doctor_api')->user()->id)->where('date', $request->date)
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
            'name'=>$patient->name,
            'user_id' => '0',
            'doctor_id' => auth('doctor_api')->user()->id,
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

        $appointments = Appointment::where('doctor_id', auth('doctor_api')->user()->id)->where('date', $request->date)
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

      public function search(Request $request){
          $validator = Validator::make($request->all(), [
            'name'=>'required'
        ]);
         $patients = patient::where('doctor_id', auth('doctor_api')->user()->id)
                            ->where('name', 'like', '%' . $request->name . '%')
                            ->get();

        return response()->json(['patients'=>$patients]);



      }





}

