<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/categories', [CategoryController::class, 'getAll']);
    Route::get('/category/{id}', [CategoryController::class, 'get']);
    Route::post('/category', [CategoryController::class, 'add']);
    Route::put('/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'delete']);
    Route::get('categories-tree', [CategoryController::class, 'tree']);

    Route::get('/products', [ProductController::class, 'getAll']);
    Route::get('/product/{id}', [ProductController::class, 'get']);
    Route::post('/product', [ProductController::class, 'add']);
    Route::put('/product/{id}', [ProductController::class, 'update']);
    Route::delete('/product/{id}', [ProductController::class, 'delete']);

    //To know how to upload images and Test routes
    Route::post('/upload', [ProductController::class, 'upload']);
    Route::get('/products/{categoryId}', [ProductController::class, 'getAllProductsForCategory']);
});
