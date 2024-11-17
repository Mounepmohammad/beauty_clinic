<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\service;
use App\Models\Appointment;
use Validator;


class user_controller extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth('api')->attempt($validator->validated())) {
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
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function Profile() {
        return response()->json(auth('api')->user());
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
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }





    public function reserve_appointment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date|date_format:Y-m-d',
            'fromtime' => 'nullable|date_format:H:i',
            'totime' => 'nullable|date_format:H:i|after:fromtime',
            'servicename' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        // الحصول على اسم الخدمة
        $service = service::where('name', $request->servicename)->first();

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
         // الحصول على معرف الطبيب من الخدمة
         $doctorId = $service->doctor_id;


        $appointment = Appointment::create(array_merge(
            $validator->validated(),
           [

            'user_id' => auth('api')->user()->id,
            'name' => auth('api')->user()->name,
            'doctor_id' => $doctorId,
            'state'=>'processing'

            ]

        ));
        return response()->json([

            'message' => 'appointment successfully reserve ',
            'appointment' => $appointment

        ],201);

    }

    public function user_appointments(Request $request){

        $appointments = auth('api')->user()->appointments;
        return response()->json(['appointments'=>$appointments]);



    }
    public function control_appointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'id'=>'required',
          'state' => 'required|in:accept,reject',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $appointment = Appointment::find($request->id);

        if (!$appointment) {
            return response()->json(['message' => 'appointment not found'], 404);
        }

        // التحقق من أن المستخدم الحالي هو المريض المرتبط بالموعد
        if ($appointment->user_id !== auth('api')->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointment->state = $request->state;

        $appointment->save();

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment
        ]);
    }
}
