<?php

use App\Http\Controllers\UsersController;
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
use App\Http\Controllers\AgentesController;

Route::controller(AuthController::class)->group(function(){
    Route::get('/registration','registration')->middleware('isLoggedIn');
    Route::post('/registration-user','registerUser')->name('isLoggedIn');
    Route::get('/login','login')->middleware('alreadyLoggedIn');
    Route::post('/login-user','loginUser')->name('login-user');
    Route::get('/logout','logout')->name('logout');
});

Route::controller(DeviceController::class)
    ->middleware('isLoggedIn')
    ->group(function () {
        Route::get('devices', 'index')->name('devices.index');
        Route::get('devices/create', 'create')->name('devices.create');
        Route::post('devices/store', 'store')->name('devices.store');
        Route::get('devices/{id}/edit', 'edit')->name('devices.edit');
        Route::post('devices/{id}/update', 'update')->name('devices.update');
        Route::get('devices/{id}/populate', 'populate')->name('devices.populate');
        Route::get('devices/{id}/restart', 'restart')->name('devices.restart');
        Route::get('devices-log', 'deviceLog')->name('devices.deviceLog');
        Route::get('finger-log', 'fingerLog')->name('devices.fingerLog');
        Route::get('fingerprints', 'fingerprints')->name('devices.fingerprints');
        Route::get('attendance', 'attendance')->name('devices.attendance');
        Route::get('devices/delete/employee', 'deleteEmployeeRecord')->name('devices.deleteEmployeeRecord');
        Route::post('devices/delete/employee', 'runDeleteFingerRecord')->name('devices.runDeleteFingerRecord');
        Route::get('devices/retrieve/fingerdata', 'retrieveFingerData')->name('devices.retrieveFingerData');
    });

Route::controller(AgentesController::class)
    ->middleware('isLoggedIn')
    ->group(function(){    
        Route::get('agentes', 'index')->name('agentes.index');
        Route::get('agentes/pull', 'pullAgentes')->name('agentes.pull');
        Route::post('agentes/runpull', 'runPullAgentes')->name('agentes.runpull');
    });

// handshake
Route::get('/iclock/cdata', [iclockController::class, 'handshake']);
// request dari device
Route::post('/iclock/cdata', [iclockController::class, 'receiveRecords']);
Route::post('/iclock/devicecmd', [iclockController::class, 'deviceCommand']);
Route::get('/iclock/test', [iclockController::class, 'test']);
Route::get('/iclock/getrequest', [iclockController::class, 'getrequest']);
Route::get('/iclock/rtdata', [iclockController::class, 'rtdata']);
Route::post('/iclock/querydata', [iclockController::class, 'querydata']);




Route::get('/', function () {
    return redirect('devices') ;
});
