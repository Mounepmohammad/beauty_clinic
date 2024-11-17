<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class login_controller extends Controller
{
   /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // محاولة تسجيل الدخول كـ Doctor
        if ($token = $this->attemptLogin($validator->validated(), 'doctors')) {
            return $this->createNewToken($token, 'doctor');
        }

        // محاولة تسجيل الدخول كـ Secretary
        if ($token = $this->attemptLogin($validator->validated(), 'secretaries')) {
            return $this->createNewToken($token, 'secretary');
        }

        // محاولة تسجيل الدخول كـ Admin
        if ($token = $this->attemptLogin($validator->validated(), 'admins')) {
            return $this->createNewToken($token, 'admin');
        }


        return response()->json(['error' => 'Unauthorized'], 401);
    }

    private function attemptLogin($credentials, $guard)
    {
        // config(['auth.defaults.guard' => $guard]);

        if ($token = auth()->guard($guard)->attempt($credentials)) {
            return $token;
        }

        return false;
    }

    protected function createNewToken($token, $userType)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('')->factory()->getTTL() * 60,
            'user' => auth()->guard()->user(),
            'user_type' => $userType
        ]);
    }
}
