<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', '\App\modules\auth\controller\Auth::index');
$routes->get('dashboard', '\App\modules\dashboard\Controller\Dashboard::index');

// // $routes->get('auth', 'Auth::index');
// $routes->get('auth', 'App\modules\auth\controller\Auth::index');
// $routes->post('auth/authValidate', 'Auth::authValidate');
// $routes->get('auth/logout', 'Auth::destroy');


$routes->group('auth', ['namespace' => 'App\modules\auth\Controller'], function($routes){
    $routes->get('/', 'Auth::index');
    $routes->post('validate', 'Auth::authValidate');
    $routes->get('logout', 'Auth::destroy');
});