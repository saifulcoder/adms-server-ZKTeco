<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\iclockController;

Route::controller(AuthController::class)->group(function(){
    Route::get('/registration','registration')->middleware('alreadyLoggedIn');
    Route::post('/registration-user','registerUser')->name('register-user');
    Route::get('/login','login')->middleware('alreadyLoggedIn');
    Route::post('/login-user','loginUser')->name('login-user');
    Route::get('/logout','logout');
    Route::get('devices', [DeviceController::class, 'Index'])->name('devices.index')->middleware('isLoggedIn');
    Route::get('devices/create', [DeviceController::class, 'Create'])->name('devices.create')->middleware('isLoggedIn');
    Route::post('devices/store', [DeviceController::class, 'Store'])->name('devices.store')->middleware('isLoggedIn');
    Route::get('devices-log', [DeviceController::class, 'DeviceLog'])->name('devices.DeviceLog')->middleware('isLoggedIn');
    Route::get('finger-log', [DeviceController::class, 'FingerLog'])->name('devices.FingerLog')->middleware('isLoggedIn');
    Route::get('attendance', [DeviceController::class, 'Attendance'])->name('devices.Attendance')->middleware('isLoggedIn');

});

// handshake
Route::get('/iclock/cdata', [iclockController::class, 'handshake']);
// request dari device
Route::post('/iclock/cdata', [iclockController::class, 'receiveRecords']);

Route::get('/iclock/test', [iclockController::class, 'test']);
Route::get('/iclock/getrequest', [iclockController::class, 'getrequest']);




Route::get('/', function () {
    return redirect('devices') ;
});
