<?php
use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\LocationSelector;
use App\Services\CacheManager;
use Illuminate\Support\Facades\Cache;

require __DIR__.'/vendor/autoload.php';

// Override env before the app reads config from .env (docker host "db" is not resolvable here).
putenv('DB_CONNECTION=mysql');
putenv('DB_HOST=127.0.0.1');
putenv('DB_PORT=3306');
putenv('DB_DATABASE=reservas');
putenv('DB_USERNAME=root');
putenv('DB_PASSWORD=rootpassword');
putenv('CACHE_STORE=array');

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Use a throwaway schema in the real MySQL DB so we don't touch real data.
$schema = 'test_reserva_fix';
$db = DB::connection()->getPdo();

try { $db->exec("DROP SCHEMA IF EXISTS `$schema`"); } catch (\Throwable $e) {}
$db->exec("CREATE SCHEMA `$schema`");

// Point a fresh connection at the test schema, then run the app's migrations there.
$config = config('database.connections.mysql');
$config['database'] = $schema;
config()->set('database.connections.mysql', $config);
config()->set('database.default', 'mysql');

DB::purge();
$connection = DB::connection('mysql');

// Run migrations into the test schema. We can't use `php artisan migrate`
// directly because the reservas migration adds an invalid index on the JSON
// `mesa_ids` column (MySQL 8 disallows indexing a JSON column without a
// generated column). Re-create the two relevant tables manually instead.
$connection = DB::connection('mysql');
$sm = $connection->getSchemaBuilder();
$sm->create('mesas', function (Blueprint $table) {
    $table->id();
    $table->enum('ubicacion', ['A', 'B', 'C', 'D']);
    $table->string('numero');
    $table->integer('capacidad')->unsigned();
    $table->timestamps();
});
$sm->create('reservas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->json('mesa_ids');
    $table->date('fecha_reserva');
    $table->time('hora_inicio');
    $table->time('hora_fin');
    $table->integer('cantidad_personas');
    $table->enum('estado', ['confirmada', 'cancelada'])->default('confirmada');
    $table->timestamps();
    $table->index(['fecha_reserva', 'estado']);
    $table->index(['fecha_reserva', 'estado', 'hora_inicio']);
});
$connection->getSchemaBuilder()->create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

// Need the blueprint import.
use Illuminate\Database\Schema\Blueprint;

// Seed mesas exactly like the project's MesaSeeder.
$ubicaciones = ['A', 'B', 'C', 'D'];
$nombres = [
    'A' => ['Terraza 1', 'Terraza 2', 'Terraza 3', 'Terraza 4'],
    'B' => ['Sala 1', 'Sala 2', 'Sala 3', 'Sala 4'],
    'C' => ['Privado 1', 'Privado 2', 'Privado 3', 'Privado 4'],
    'D' => ['Barra 1', 'Barra 2', 'Barra 3', 'Barra 4'],
];
$capacidades = [
    'A' => [4, 6, 4, 8],
    'B' => [4, 4, 6, 10],
    'C' => [6, 8, 4, 12],
    'D' => [2, 4, 4, 6],
];
foreach ($ubicaciones as $u) {
    for ($i = 0; $i < 4; $i++) {
        Mesa::create(['ubicacion' => $u, 'numero' => $nombres[$u][$i], 'capacidad' => $capacidades[$u][$i]]);
    }
}

// Point the CacheManager-backed LocationSelector at the test schema.
$cache = $app->make(CacheManager::class);
$selector = new LocationSelector($cache);

echo "=== TEST: 5 reservations at the SAME slot => all different tables ===\n";

// Saturday 2026-08-22 (validateBusinessHours: Sat 22:00-02:00).
$fecha = '2026-08-22';
$horaInicio = '22:00';
$personas = 4;

$assigned = [];
$allOk = true;
for ($i = 1; $i <= 5; $i++) {
    $reserva = $selector->autoAssign($i, $fecha, $horaInicio, $personas);
    if (! $reserva) {
        echo "Reservation #{$i}: FAILED to assign (no availability)\n";
        $allOk = false;
        continue;
    }
    $mesas = Mesa::whereIn('id', $reserva->mesa_ids)->orderBy('id')->get()->map(fn ($m) => "{$m->ubicacion}-{$m->numero}(id={$m->id},cap={$m->capacidad})")->join(', ');
    $assigned = array_unique(array_merge($assigned, $reserva->mesa_ids));
    echo "Reservation #{$i}: mesas = {$mesas}\n";
}

echo "\nTotal unique tables assigned across 5 same-slot reservations: " . count($assigned) . "\n";
if (count($assigned) === 5) {
    echo "PASS: every reservation got a DISTINCT table (no double booking)\n";
} else {
    echo "FAIL: duplicate tables assigned!\n";
    $allOk = false;
}

// Verify NO double-booking exists in the DB itself.
$allReservas = Reserva::whereDate('fecha_reserva', $fecha)->confirmed()->get();
$doubleBooked = [];
foreach ($allReservas as $r) {
    // For this slot, overlapping reservations share a table?
    foreach ($allReservas as $r2) {
        if ($r->id >= $r2->id) continue;
        $shared = array_intersect($r->mesa_ids, $r2->mesa_ids);
        if (!empty($shared)) {
            $doubleBooked[] = "reserva {$r->id} & {$r2->id} share mesa " . implode(',', $shared);
        }
    }
}
if (empty($doubleBooked)) {
    echo "PASS: no shared tables between overlapping reservations in DB\n";
} else {
    echo "FAIL: double bookings found:\n  - " . implode("\n  - ", $doubleBooked) . "\n";
    $allOk = false;
}

// Cleanup
try { $db->exec("DROP SCHEMA `$schema`"); echo "\n(cleaned up test schema)\n"; } catch (\Throwable $e) {}

echo ($allOk ? "\nRESULT: ALL CHECKS PASSED\n" : "\nRESULT: FAILURES DETECTED\n");
exit($allOk ? 0 : 1);
