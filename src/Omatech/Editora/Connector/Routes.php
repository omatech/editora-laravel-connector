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

Route::group(['middleware' => ['web']], function()
{
    $routes = config('editora.routeParams');

    foreach($routes as $route)
    {
        $routeString = '';

        foreach($route as $param)
        {
            $routeString .= '/{'.$param.'?}';
        }

        Route::get($routeString, 'Omatech\Editora\Connector\EditoraController@init');
    }
});
