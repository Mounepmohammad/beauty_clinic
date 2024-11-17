<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_controller;
use App\Http\Controllers\doctor_controller;
use App\Http\Controllers\secrtary_controller;
use App\Http\Controllers\admin_controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    // 'middleware' => 'api',
    'prefix' => 'user'

], function ($router) {
    Route::post('/login', [user_controller::class, 'login'])->name('login');
    Route::post('/register', [user_controller::class, 'register']);
    Route::post('/logout', [user_controller::class, 'logout']);
    Route::get('/profile', [user_controller::class, 'Profile']);

    Route::post('/reserve', [user_controller::class, 'reserve_appointment']);
    Route::post('/user_appointments', [user_controller::class, 'user_appointments']);


});



Route::group([
    // 'middleware' => 'doctor.auth',
    'prefix' => 'doctor'

], function ($router) {
    Route::post('/doctor_login', [doctor_controller::class, 'doctor_login']);
    Route::post('/doctor_register', [doctor_controller::class, 'doctor_register']);
    Route::post('/doctor_logout', [doctor_controller::class, 'doctor_logout']);
    Route::get('/doctor_profile', [doctor_controller::class, 'doctor_Profile']);

    Route::post('/my_appointments', [doctor_controller::class, 'my_appointments']);

    Route::post('/add_patient', [doctor_controller::class, 'add_patient']);
    Route::post('/update_patient', [doctor_controller::class, 'update_patient']);
    Route::post('/delete_patient', [doctor_controller::class, 'delete_patient']);

    Route::post('/add_record', [doctor_controller::class, 'add_record']);
    Route::post('/update_record', [doctor_controller::class, 'update_record']);
    Route::post('/delete_record', [doctor_controller::class, 'delete_record']);

    Route::post('/my_patients', [doctor_controller::class, 'my_patients']);
    Route::post('/patient_record', [doctor_controller::class, 'patient_record']);

    Route::post('/reserve_appointment', [doctor_controller::class, 'reserve_appointment']);
    Route::post('/update_appointment', [doctor_controller::class, 'update_appointment']);
    Route::post('/delete_appointment', [doctor_controller::class, 'delete_appointment']);




});

Route::group([
    // 'middleware' => 'doctor.auth',
    'prefix' => 'secrtary'

], function ($router) {
    Route::post('/secrtary_login', [secrtary_controller::class, 'secrtary_login']);
    Route::post('/secrtary_register', [secrtary_controller::class, 'secrtary_register']);
    Route::post('/secrtary_logout', [secrtary_controller::class, 'secrtary_logout']);
    Route::get('/secrtary_profile', [secrtary_controller::class, 'secrtary_profile']);

    Route::post('/doctors', [secrtary_controller::class, 'doctors']);
    Route::post('/services', [secrtary_controller::class, 'services']);
    Route::post('/doctor_appointments', [secrtary_controller::class, 'doctor_appointments']);
    Route::post('/reserve_appointment', [secrtary_controller::class, 'reserve_appointment']);
    Route::post('/update_appointment', [secrtary_controller::class, 'update_appointment']);
    Route::post('/delete_appointment', [secrtary_controller::class, 'delete_appointment']);
});

Route::group([
    // 'middleware' => 'doctor.auth',
    'prefix' => 'admin'

], function ($router) {
    Route::post('/admin_login', [admin_controller::class, 'admin_login']);
    Route::post('/admin_register', [admin_controller::class, 'admin_register']);
    Route::post('/admin_logout', [admin_controller::class, 'admin_logout']);
    Route::get('/admin_profile', [admin_controller::class, 'admin_profile']);

    Route::post('/doctors', [admin_controller::class, 'doctors']);
    Route::post('/add_doctor', [admin_controller::class, 'add_doctor']);
    Route::post('/update_doctor', [admin_controller::class, 'update_doctor']);
    Route::post('/delete_doctor', [admin_controller::class, 'delete_doctor']);

    Route::post('/secrtaries', [admin_controller::class, 'secrtaries']);
    Route::post('/add_secrtary', [admin_controller::class, 'add_secrtary']);
    Route::post('/update_secrtary', [admin_controller::class, 'update_secrtary']);
    Route::post('/delete_secrtary', [admin_controller::class, 'delete_secrtary']);

    Route::post('/services', [admin_controller::class, 'services']);
    Route::post('/add_service', [admin_controller::class, 'add_service']);
    Route::post('/update_service', [admin_controller::class, 'update_service']);
    Route::post('/delete_service', [admin_controller::class, 'delete_service']);

    Route::post('/offers', [admin_controller::class, 'offers']);
    Route::post('/add_offer', [admin_controller::class, 'add_offer']);
    Route::post('/update_offer', [admin_controller::class, 'update_offer']);
    Route::post('/delete_offer', [admin_controller::class, 'delete_offer']);

    Route::post('/admins', [admin_controller::class, 'admins']);
    Route::post('/add_admin', [admin_controller::class, 'add_admin']);
    Route::post('/update_admin', [admin_controller::class, 'update_admin']);
    Route::post('/delete_admin', [admin_controller::class, 'delete_admin']);
});
