<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('home');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard/table', function () {
        return view('dashboardtable');
    })->name('dashboard.table');


        Route::get('/dashboard/billing', function () {
        return view('dashboardbilling');
    })->name('dashboard.billing');
    
    Route::get('/dashboard/profile', function () {
        return view('dashboardprofile');
    })->name('dashboard.profile');


       Route::get('/dashboard/network', function () {
        return view('dashboardnetwork');
    })->name('dashboard.network');

          Route::get('/dashboard/notification', function () {
        return view('dashboardnotification');
    })->name('dashboard.notification');


    Route::get('/dashboard/package', function () {
        return view('dashboardpackage');
    })->name('dashboard.package');


});









Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
