<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', '\App\modules\auth\Controllers\Auth::index');

// Dashboard Patients
$routes->get('dashboard', '\App\modules\dashboard\Controllers\Dashboard::index');
$routes->post('patients/fetch2', '\App\modules\patients\Controllers\Patients::fetch2');

$routes->group('auth', ['namespace' => 'App\modules\auth\Controllers'], function($routes){
    $routes->get('/', 'Auth::index');
    $routes->post('validate', 'Auth::authValidate');
    $routes->get('logout', 'Auth::destroy');
});