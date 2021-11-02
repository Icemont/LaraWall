<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
    'as' => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('servers', ServerController::class);
    $router->resource('services', ServiceController::class);
    $router->resource('customers', CustomerController::class);
    $router->resource('customer-ips', CustomerIpController::class);
    $router->resource('packages', PackageController::class);
    $router->resource('subscriptions', SubscriptionController::class);
});
