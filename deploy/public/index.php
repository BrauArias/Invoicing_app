<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../../laravel_factura/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader — apunta a laravel_factura (fuera de www/)
require __DIR__.'/../../laravel_factura/vendor/autoload.php';

// Bootstrap Laravel
(require_once __DIR__.'/../../laravel_factura/bootstrap/app.php')
    ->handleRequest(Request::capture());
