<?php

use App\Http\Controllers\v1\MedicalRecordController;
use App\Http\Controllers\v1\UserController;
use App\Http\Controllers\v1\PetController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\SchedulingController;
use App\Http\Controllers\v1\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function() {
    Route::apiResource("/users", UserController::class);
    Route::apiResource("/pets", PetController::class);
    Route::apiResource("/services", ServiceController::class);
    Route::apiResource("/schedulings", SchedulingController::class);
    Route::get("/medicalrecords/result", [MedicalRecordController::class, "result"]);
    Route::apiResource("/medicalrecords", MedicalRecordController::class);
    Route::post("/auth", [AuthController::class, "login"]);
    Route::post("/logout", [AuthController::class, "logout"]);
});