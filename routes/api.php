<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChargingSessionController;
use App\Http\Controllers\Api\StationController; 
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\StatistiqueController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    
   
    Route::get('/user', function (Request $request){
        return $request->user();
    });

    Route::post('/reservations', [ReservationController::class, 'store']);

    Route::put('/reservations/{id}', [ReservationController::class, 'update']);

    Route::patch('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);


    Route::post('/sessions/start', [ChargingSessionController::class, 'startCharging']);
    
   
    Route::put('/sessions/{sessionId}/stop', [ChargingSessionController::class, 'stopCharging']);
  

    
    Route::get('/sessions/current', [ReservationController::class, 'currentSessions']);
    
   
    Route::get('/sessions/past', [ReservationController::class, 'pastSessions']);

    

    Route::middleware('admin')->group(function () {
        Route::post('/stations', [StationController::class, 'store']);      
        Route::put('/stations/{id}', [StationController::class, 'update']);   
        Route::delete('/stations/{id}', [StationController::class, 'destroy']);
        Route::get('/admin/stats', [StatistiqueController::class, 'getGlobalStats']);
    });


    Route::get('/stations', [StationController::class, 'index']); 
    Route::get('/stations/{id}', [StationController::class, 'show']); 
});