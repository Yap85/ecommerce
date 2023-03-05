<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
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
    return view('auth/login');
});

Route::get('/newProduct', function () {
    return view('productform');
});

Route::get('/updateProduct', function () {
    return view('updateProduct');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/ajax/productList', [ProductController::class, 'getProductList']);
Route::get('/ajax/viewDetail', [ProductController::class, 'viewDetails']);

Route::post('/ajax/submit-form',[ProductController::class, 'newProduct']);

Route::get('/ajax/editProductDetail',[ProductController::class, 'viewDetails']);
Route::post('/ajax/submitUpdateProductForm',[ProductController::class, 'updateProduct']);
Route::post('/ajax/deleteProduct',[ProductController::class, 'deleteProduct']);

require __DIR__.'/auth.php';
