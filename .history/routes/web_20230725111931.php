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
use App\Http\Controllers\AbsensiSholatController;

Route::resource('absensi_sholat', AbsensiSholatController::class);
Route::resource('devices', DeviceController::class);


// handshake
Route::get('/iclock/cdata', 'iclockController@checkDevice');
// request dari device
Route::post('/iclock/cdata', 'iclockController@receiveRecords');

Route::get('/', function () {
    return view('welcome');
});
