<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LARAVEL APP CHECK ===\n";
echo "APP_KEY: ".(env('APP_KEY')?'SET':'NOT')."\n";
echo "View landing: ".(file_exists(__DIR__."/resources/views/landing.blade.php")?'OK':'MISSING')."\n";
echo "AuthController: ".(class_exists('App\Http\Controllers\AuthController')?'OK':'MISSING')."\n";
echo "DashboardController: ".(class_exists('App\Http\Controllers\DashboardController')?'OK':'MISSING')."\n";
echo "ReservacionController: ".(class_exists('App\Http\Controllers\ReservacionController')?'OK':'MISSING')."\n";
echo "CacheManager: ".(class_exists('App\Services\CacheManager')?'OK':'MISSING')."\n";
echo "LocationSelector: ".(class_exists('App\Services\LocationSelector')?'OK':'MISSING')."\n";
echo "Mesa Model: ".(class_exists('App\Models\Mesa')?'OK':'MISSING')."\n";
echo "Reserva Model: ".(class_exists('App\Models\Reserva')?'OK':'MISSING')."\n";
echo "User Model: ".(class_exists('App\Models\User')?'OK':'MISSING')."\n";
echo "AuthCheck Middleware: ".(class_exists('App\Http\Middleware\AuthCheck')?'OK':'MISSING')."\n";

try {
    echo "Trying to render landing... ";
    $view = view('landing')->render();
    echo "SUCCESS (".strlen($view)." bytes)\n";
} catch (Exception $e) {
    echo "FAILED: ".$e->getMessage()."\n";
}
