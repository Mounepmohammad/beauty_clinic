<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\admin;
use App\Models\doctor;
use App\Models\secrtary;
use App\Models\service;
use App\Models\offer;


use Validator;


class admin_controller extends Controller
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
        $this->middleware('admin.auth', ['except' => [
            'admin_login', 'admin_register'
        ]]);
        }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth('admin_api')->attempt($validator->validated())) {
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
    public function admin_register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:admins',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $admin = admin::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'admin successfully registered',
            'admin' => $admin
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_logout() {
        auth('admin_api')->logout();

        return response()->json(['message' => 'admin successfully signed out'],201);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin_Profile() {
        return response()->json(auth('admin_api')->user());
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
            'expires_in' => auth('admin_api')->factory()->getTTL() * 60,
            'admin' => auth('admin_api')->user()
        ]);
    }


 ///////////////////////////////////////////DOCTORS MANAGE//////////////////////////
    public function doctors()
    {
        $doctors = doctor::all();
        return response()->json(['doctors'=> $doctors]);
    }

    // إضافة دكتور جديد
    public function add_doctor(Request $request)
    {
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
            'message' => 'doctor successfully added',
            'doctor' => $doctor
        ], 201);
    }

    // // عرض تفاصيل دكتور معين
    // public function show_doctor($id)
    // {
    //     $doctor = Doctor::find($id);

    //     if (!$doctor) {
    //         return response()->json(['message' => 'Doctor not found'], 404);
    //     }

    //     return response()->json($doctor);
    // }

    // تحديث معلومات دكتور معين
    public function update_doctor(Request $request)
    {
        $doctor = doctor::find($request->id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|between:2,100',
            'email' => 'sometimes|required|string|email|max:100|unique:doctors,email,' . $request->id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $doctor->update(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'doctor successfully updated',
            'doctor' => $doctor
        ], 201);
    }

    // حذف دكتور معين
    public function delete_doctor(Request $request)
    {
        $doctor = doctor::find($request->id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->delete();
        return response()->json(['message' => 'Doctor deleted successfully']);
    }


    ///////////////////////////////////////////SECRTARY MANAGE//////////////////////////

    public function secrtaries()
    {
        $secrtaries = secrtary::all();
        return response()->json(['secrtaries'=> $secrtaries]);
    }

    // إضافة دكتور جديد
    public function add_secrtary(Request $request)
    {
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
            'message' => 'doctor successfully added',
            'secrtary' => $secrtary
        ], 201);
    }

    // // عرض تفاصيل دكتور معين
    // public function show_doctor($id)
    // {
    //     $doctor = Doctor::find($id);

    //     if (!$doctor) {
    //         return response()->json(['message' => 'Doctor not found'], 404);
    //     }

    //     return response()->json($doctor);
    // }

    // تحديث معلومات دكتور معين
    public function update_secrtary(Request $request)
    {
        $secrtary = secrtary::find($request->id);

        if (!$secrtary) {
            return response()->json(['message' => 'secrtary not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|between:2,100',
            'email' => 'sometimes|required|string|email|max:100|unique:secrtaries,email,' . $request->id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $secrtary->update(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'secrtary successfully updated',
            'secrtary' => $secrtary
        ], 201);
    }

    // حذف دكتور معين
    public function delete_secrtary(Request $request)
    {
        $secrtary = secrtary::find($request->id);

        if (!$secrtary) {
            return response()->json(['message' => 'secrtary not found'], 404);
        }

        $secrtary->delete();
        return response()->json(['message' => 'secrtary deleted successfully']);
    }




    ///////////////////////////////////////////service MANAGE//////////////////////////

    public function services()
    {
        $services = service::all();
        return response()->json(['services'=> $services]);
    }

    // إضافة دكتور جديد
    public function add_service(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
          // معالجة رفع الصورة
          if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validatedData['photo'] = $path;
          }
        $service = service::create(array_merge(
            $validator->validated(),
        ));
        return response()->json([
            'message' => 'service successfully added',
            'service' => $service
        ], 201);
    }

    // // عرض تفاصيل دكتور معين
    // public function show_doctor($id)
    // {
    //     $doctor = Doctor::find($id);

    //     if (!$doctor) {
    //         return response()->json(['message' => 'Doctor not found'], 404);
    //     }

    //     return response()->json($doctor);
    // }

    // تحديث معلومات دكتور معين
    public function update_service(Request $request)
    {
        $service = service::find($request->id);

        if (!$service) {
            return response()->json(['message' => 'service not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
        ]);

          // معالجة رفع الصورة إذا وُجِدت
          if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }

            $path = $request->file('photo')->store('photos', 'public');
            $validatedData['photo'] = $path;
        }

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $service->update(array_merge(
            $validator->validated(),
        ));
        return response()->json([
            'message' => 'service successfully updated',
            'service' => $service
        ], 201);
    }

    // حذف دكتور معين
    public function delete_service(Request $request)
    {
        $service = service::find($request->id);

        if (!$service) {
            return response()->json(['message' => 'service not found'], 404);
        }

        $service->delete();
        return response()->json(['message' => 'service deleted successfully']);
    }
////////////////////////////////////////////////////////OFFERS MANAGE////////////////////////


public function offers()
{
    $offers = offer::all();
    return response()->json(['offers'=> $offers]);
}

// إضافة دكتور جديد
public function add_offer(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }
      // معالجة رفع الصورة
      if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('photos', 'public');
        $validatedData['photo'] = $path;
      }
    $offer = offer::create(array_merge(
        $validator->validated(),
    ));
    return response()->json([
        'message' => 'offer successfully added',
        'offer' => $offer
    ], 201);
}


// تحديث معلومات دكتور معين
public function update_offer(Request $request)
{
    $offer = offer::find($request->id);

    if (!$offer) {
        return response()->json(['message' => 'offer not found'], 404);
    }


    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

      // معالجة رفع الصورة إذا وُجِدت
      if ($request->hasFile('photo')) {
        // حذف الصورة القديمة إذا كانت موجودة
        if ($offer->photo) {
            Storage::disk('public')->delete($offer->photo);
        }

        $path = $request->file('photo')->store('photos', 'public');
        $validatedData['photo'] = $path;
    }

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $offer->update(array_merge(
        $validator->validated(),
    ));
    return response()->json([
        'message' => 'offer successfully updated',
        'service' => $offer
    ], 201);
}

// حذف دكتور معين
public function delete_offer(Request $request)
{
    $offer = offer::find($request->id);

    if (!$offer) {
        return response()->json(['message' => 'offer not found'], 404);
    }

    $offer->delete();
    return response()->json(['message' => 'offer deleted successfully']);
}

/////////////////////////////////////////////// MANAGE ADMINS////////////////////////

public function admins()
{
    $admins = admin::all();
    return response()->json(['admins'=> $admins]);
}

// إضافة دكتور جديد
public function add_admin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:admins',
        'password' => 'required|string|min:6',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $admin = admin::create(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
    ));
    return response()->json([
        'message' => 'admin successfully added',
        'admin' => $admin
    ], 201);
}

public function update_admin(Request $request)
{
    $admin = admin::find($request->id);

    if (!$admin) {
        return response()->json(['message' => 'admin not found'], 404);
    }


    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|between:2,100',
        'email' => 'sometimes|required|string|email|max:100|unique:admins,email,' . $request->id,
        'password' => 'sometimes|required|string|min:6',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $admin->update(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
    ));
    return response()->json([
        'message' => 'admin successfully updated',
        'admin' => $admin
    ], 201);
}

// حذف دكتور معين
public function delete_admin(Request $request)
{
    $admin = admin::find($request->id);

    if (!$admin) {
        return response()->json(['message' => 'admin not found'], 404);
    }

    $admin->delete();
    return response()->json(['message' => 'admin deleted successfully']);
}

}

