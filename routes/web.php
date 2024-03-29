<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    // 'auth:sanctum',
    // config('jetstream.auth_session'),
    // 'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/clients/grid', 'ClientController@grid')->name('clients_grid');
    Route::post('/clients/grid-data', 'ClientController@gridData')->name('clients_grid_data');
    Route::put('/clients/{client}', 'ClientController@update')->name('clients_update');
    Route::post('/clients', 'ClientController@store')->name('clients_store');
    Route::delete('clients/{client}', 'ClientController@delete')->name('clients_delete');
    Route::get('/clients', 'ClientController@index')->name('clients');


    Route::get('/tags', 'TagController@index')->name('tags');

    Route::post('/tags', 'TagController@createTag')->name('tag_create');
    Route::post('/categories', 'TagController@createCategory')->name('category_create');

    Route::put('/tags/{tag}', 'TagController@updateTag')->name('tag_update');
    Route::put('/categories/{category}', 'TagController@updateCategory')->name('category_update');

    Route::delete('tags/{tag}', 'TagController@deleteTag')->name('tag_delete');
    Route::delete('categories/{category}', 'TagController@deleteCategory')->name('category_delete');

});
