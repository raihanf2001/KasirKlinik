<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\is_admin;
use Illuminate\Support\Facades\Route;
Route::get('/login',[AuthController::class,'index'])->name('login');
Route::post('/masuk',[AuthController::class,'authenticate']);
Route::middleware('auth')->group(function (){
Route::post('/logout',[AuthController::class,'logout']);
Route::get('/pos', [PosController::class, 'index']);
Route::post('/admin/pos/store', [PosController::class, 'store']);
Route::get('/admin/pos/search', [PosController::class, 'search']);
Route::get('/admin/pos/receipt/{id}', [PosController::class, 'receipt']);
Route::middleware(is_admin::class)->group(function (){
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/admin/products/search', [ProductController::class, 'search']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/admin/settings', [SettingController::class, 'index']);
    Route::post('/admin/settings', [SettingController::class, 'update']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::put('/admin/users/{user}', [UserController::class, 'update']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);
    Route::get('/admin/report', [ReportController::class, 'index']);
    Route::get('/admin/report/pdf', [ReportController::class, 'exportPdf']);
    Route::get('/admin/pos/export-excel', [ReportController::class, 'exportExcel'])->name('report.exportExcel');
    Route::resource('admin/products', ProductController::class);
});

});