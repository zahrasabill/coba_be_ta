<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PenangananController;


Route::post('login', [AuthController::class, 'login']);

Route::post('videos', [VideoController::class, 'store']);

Route::middleware('jwt.auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    Route::get('me', [UserController::class, 'me']);
    
    Route::get('videos/stream/{filename}', [VideoController::class, 'streamVideo']);

    // Route Admin dan Dokter
    Route::middleware('role:admin|dokter')->group(function () {
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'delete']);
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete']);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);
        
    });

    // Role Pasien dan Dokter - untuk Penanganan
    Route::middleware('role:dokter|pasien')->group(function () {
        Route::get('videos', [VideoController::class, 'showAllVideos']); // Lihat semua video
        
        // Penanganan routes - Pasien dan Dokter bisa melihat
        Route::get('penanganan', [PenangananController::class, 'index']);
        Route::get('penanganan/{id}', [PenangananController::class, 'show']);
    });
    
    // Route Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('dokter', [UserController::class, 'getAllDokter']);
        Route::post('register/dokter', [UserController::class, 'registerDokter']);
    });

    // Route Dokter
    Route::middleware('role:dokter')->group(function () {
        Route::get('videos/pasien', [UserController::class, 'showVideosByPasienId']);
        Route::post('videos/{videoId}/assign/{userId}', [VideoController::class, 'assignToUser']);
        Route::patch('/videos/{videoId}', [VideoController::class, 'updateStatusVideo']);
        Route::patch('v1/videos/{videoId}/keterangan', [VideoController::class, 'updateKeterangan']);
        
        Route::get('pasien', [UserController::class, 'getAllPasien']);
        Route::post('register/pasien', [UserController::class, 'registerPasien']);
        
        // Penanganan routes - Hanya Dokter
        Route::post('penanganan', [PenangananController::class, 'store']);
        Route::put('penanganan/{id}', [PenangananController::class, 'update']);
        Route::delete('penanganan/{id}', [PenangananController::class, 'destroy']);
        Route::get('penanganan/statistik', [PenangananController::class, 'statistik']);
    });

});