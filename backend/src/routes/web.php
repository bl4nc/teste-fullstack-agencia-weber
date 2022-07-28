<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('product', 'ProductController@insertProduct');
$router->group(['prefix' => 'category'], function () use ($router) {
    $router->post('', 'CategoryController@insertCategory');
    $router->delete('{category_id}', 'CategoryController@deleteCategory');
    $router->put('{category_id}', 'CategoryController@updateCategory');
    $router->get('{category_id}', 'CategoryController@selectCategory');
    $router->get('', 'CategoryController@selectAllCategory');

});

});
