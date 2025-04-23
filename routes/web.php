<?php

use App\Http\Controllers\GmailController;
use App\Http\Controllers\GoogleAuthTestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ResetPasswordController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gmail', [GmailController::class, 'index'])->name('gmail.index');
Route::post('/gmail/save-token', [GmailController::class, 'saveToken'])->name('gmail.saveToken');
Route::post('/gmail/clear-token', [GmailController::class, 'clearToken'])->name('gmail.clearToken');

// Rutas para pruebas de autenticación con Google
Route::get('/google-test', [GoogleAuthTestController::class, 'showLoginPage'])->name('google.test');
Route::post('/google-test/process', [GoogleAuthTestController::class, 'processToken'])->name('google.test.process');
Route::get('/google-test/user', [GoogleAuthTestController::class, 'testUserData'])->name('google.test.user');
Route::get('/google-test/logout', [GoogleAuthTestController::class, 'testLogout'])->name('google.test.logout');

// Rutas de restablecimiento de contraseña
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset')
    ->where('token', '.*');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');
