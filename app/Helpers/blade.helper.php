<?php

// Paksa load autoload composer jika editor masih merah
if (file_exists(ROOTPATH . 'vendor/autoload.php')) {
    require_once ROOTPATH . 'vendor/autoload.php';
}

use Jenssegers\Blade\Blade;

if (!function_exists('blade')) {
    function blade(string $view, array $data = [])
    {
        $views = APPPATH . 'Views';
        $cache = WRITEPATH . 'cache/blade';

        if (!is_dir($cache)) {
            mkdir($cache, 0777, true);
        }

        // Gunakan \ agar mencari di root namespace
        $blade = new \Jenssegers\Blade\Blade($views, $cache);
        
        echo $blade->make($view, $data)->render();
    }
}