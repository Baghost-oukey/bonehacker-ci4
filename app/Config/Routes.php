<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', '\App\modules\auth\Controllers\Auth::index');

$routes->group('auth', ['namespace' => 'App\modules\auth\Controllers'], function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('validate', 'Auth::authValidate');
    $routes->get('destroy', 'Auth::destroy');
});

// Beranda Patients
$routes->get('beranda_views', '\App\modules\beranda\Controllers\Beranda::index');

// Rekam Medis
$routes->get('dashboard', '\App\modules\dashboard\Controllers\Dashboard::index');
$routes->post('patients/fetch2', '\App\modules\patients\Controllers\Patients::fetch2');

// Save Data - Rekam Medis
$routes->post('patient/store', '\App\modules\patients\Controllers\Patients::store');
$routes->post('patient/check_phone', '\App\modules\patients\Controllers\Patients::check_phone');

// Antrean
$routes->group('antrean', ['namespace' => 'App\modules\antrean\Controllers'], function ($routes) {
    $routes->get('/', 'Antrean::index');
    $routes->get('fetchJson', 'Antrean::fetchJson');
    $routes->post('fetchDataTable', 'Antrean::fetchDataTable');
    $routes->post('fetchPatientsDataTable', 'Antrean::fetchPatientsDataTable');
    $routes->post('destroy/(:num)', 'Antrean::destroy/$1');
});


$routes->group('region', ['namespace' => 'App\modules\region\Controllers'], function ($routes) {
    $routes->get('/', 'Region::index');    
    $routes->post('fetch', 'Region::fetch');  
    $routes->post('store', 'Region::store');
    $routes->post('update/(:num)', 'Region::update/$1');
    $routes->get('destroy/(:num)', 'Region::destroy/$1'); 
});
