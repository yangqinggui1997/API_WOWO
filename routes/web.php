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

//Auth
$router->post('auth/token', ['uses' => 'AuthController@authenticate']);
$router->post('auth/refresh', ['uses' => 'AuthController@refresh']);
$router->post('auth/loginGoogle', ['uses' => 'AuthController@authenticate_google']);
$router->post('auth/loginFB', ['uses' => 'AuthController@authenticate_fb']);
//Add User
$router->post('user/create', ['uses' => 'UserController@register_user']);
//User
$router->group(['middleware' => 'jwt.auth'], function() use ($router) {
    $router->get('user/me', 'UserController@me');
    $router->get('user/logout', 'UserController@logout');
    $router->post('user/update', 'UserController@update');
});
//Post
$router->group(['prefix' => 'post'], function() use ($router)
{
    $router->get('', ['uses' => 'PostController@getPost']);
    $router->get('get', ['uses' => 'PostController@getOrFilter']);
    $router->get('relate', ['uses' => 'PostController@getRelatePosts']);
    $router->post('like', ['uses' => 'PostController@like']);
    $router->post('unlike', ['uses' => 'PostController@disLike']);
    $router->post('savePost', ['middleware' => 'jwt.auth', 'uses' => 'PostController@savePost']);
    $router->post('unsavePost', ['middleware' => 'jwt.auth', 'uses' => 'PostController@unsavePost']);
    $router->post('filter', ['uses' => 'PostController@getOrFilter']);

});
//Video
$router->group(['prefix' => 'video'], function() use ($router) {
    $router->get('get', ['uses' => 'PostVideoController@getVideos']);
    $router->get('', ['uses' => 'PostVideoController@getVideo']);
    $router->get('relate', ['uses' => 'PostVideoController@getRelateVideos']);
    $router->post('like', ['uses' => 'PostController@like']);
    $router->post('unlike', ['uses' => 'PostController@disLike']);
    $router->post('savePost', ['middleware' => 'jwt.auth', 'uses' => 'PostController@savePost']);
    $router->post('unSavePost', ['middleware' => 'jwt.auth', 'uses' => 'PostController@unSavePost']);
});
//Comment
$router->group(['prefix' => 'comment'], function() use ($router) {
    $router->get('get', ['uses' => 'CommentController@getComment']);
    $router->put('create', ['middleware' => 'jwt.auth', 'uses' => 'CommentController@createComment']);
});
//PostImage
$router->group(['prefix' => 'postImage'], function() use ($router) {
    $router->get('get', ['uses' => 'PostImageController@getPostImage']);
});
//Category
$router->group(['prefix' => 'category'], function() use ($router) {
    $router->get('get', ['uses' => 'CategoryController@getCategory']);
    $router->post('propertiesForFilter', ['uses' => 'CategoryController@getPropertiesForFilter']);
});
//Location
$router->group(['prefix' => 'location'], function() use ($router) {
    $router->get('get', ['uses' => 'LocationController@getLocation']);
});
//upload file
$router->group(['prefix' => 'upload'], function() use ($router) {
    $router->put('', ['middleware' => 'jwt.auth', 'uses' => 'UploadFileController@uploadFile']);
});
