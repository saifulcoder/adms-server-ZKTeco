<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\iclockController;

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
// // handshake
// Route::get('/iclock/cdata', [iclockController::class, 'handshake']);
// // request dari device
// Route::post('/iclock/cdata', [iclockController::class, 'receiveRecords']);

// Route::get('/iclock/test', [iclockController::class, 'test']);
// Route::get('/iclock/getrequest', [iclockController::class, 'getrequest']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
