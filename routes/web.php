<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AdminController::class, 'index']);
Route::post('/auth', [AdminController::class, 'login_auth'])->name('admin.auth');

Route::group(['middleware' => 'admin_auth'], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/export', [AdminController::class, 'export']);
    Route::post('/settings', [AdminController::class, 'dashboard'])->name('admin.settings');
});

