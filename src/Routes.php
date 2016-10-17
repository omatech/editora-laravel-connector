<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['middleware' => ['web']], function() {
    Route::get('/', 'Omatech\Connector\EditoraController');
    Route::get('/{language}', 'Omatech\Connector\EditoraController');
    Route::get('/{language}/{nice_url}', 'Omatech\Connector\EditoraController');
});
