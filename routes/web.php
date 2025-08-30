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


// for user dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard/referrals', function () {
        return view('dashboardreferrals');
    })->name('dashboard.referrals');


        Route::get('/dashboard/payout', function () {
        return view('dashboardpayout');
    })->name('dashboard.payout');
    
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

// Admin dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin/dashboard');
    })->name('dashboard');

   

});









Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
