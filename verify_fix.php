<?php
$db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=reservas;charset=utf8mb4", "root", "rootpassword");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== mesas table (count) ===\n";
$row = $db->query("SELECT COUNT(*) FROM mesas")->fetchColumn();
echo "mesas: $row\n";

echo "=== sample mesas ===\n";
foreach ($db->query("SELECT id, ubicacion, numero, capacidad FROM mesas ORDER BY ubicacion, capacidad LIMIT 8") as $r) {
    echo "id={$r['id']} ubi={$r['ubicacion']} num={$r['numero']} cap={$r['capacidad']}\n";
}

echo "=== reservas table (count) ===\n";
$row = $db->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
echo "reservas: $row\n";

if ($row > 0) {
    echo "=== existing reservations ===\n";
    foreach ($db->query("SELECT id, mesa_ids, fecha_reserva, hora_inicio, hora_fin, cantidad_personas, estado FROM reservas ORDER BY id") as $r) {
        echo "id={$r['id']} mesa_ids={$r['mesa_ids']} fecha={$r['fecha_reserva']} {$r['hora_inicio']}-{$r['hora_fin']} pers={$r['cantidad_personas']} estado={$r['estado']}\n";
    }

    echo "\n=== TEST 1: raw JSON storage format ===\n";
    $sampleRow = $db->query("SELECT mesa_ids FROM reservas WHERE mesa_ids IS NOT NULL LIMIT 1")->fetchColumn();
    echo "Raw mesa_ids from DB: $sampleRow\n";

    $decoded = json_decode($sampleRow, true);
    $firstId = $decoded[0] ?? null;
    echo "First mesa_id: "; var_export($firstId); echo " (type: " . gettype($firstId) . ")\n";

    echo "\n=== TEST 2: JSON_CONTAINS type-strictness ===\n";
    $mesaId = $db->query("SELECT id FROM mesas ORDER BY id LIMIT 1")->fetchColumn();
    echo "mesa_id to test: $mesaId\n";

    // OLD (buggy) approach - string cast
    $old = $db->prepare("SELECT id FROM reservas WHERE JSON_CONTAINS(mesa_ids, ?)");
    $old->execute(['"' . $mesaId . '"']);
    echo "  OLD JSON_CONTAINS(mesa_ids, '\"{$mesaId}\"') [string cast]: " . $old->rowCount() . " rows matched\n";

    // NEW (fixed) approach - integer
    $new = $db->prepare("SELECT id FROM reservas WHERE JSON_CONTAINS(mesa_ids, ?)");
    $new->execute([$mesaId]);
    echo "  NEW JSON_CONTAINS(mesa_ids, '{$mesaId}') [integer]: " . $new->rowCount() . " rows matched\n";

    echo "\n=== TEST 3: Full conflict query (old vs new) for a known booked mesa today ===\n";
    $today = date("Y-m-d");
    // Find a mesa that is booked today
    $bookedToday = $db->prepare("SELECT mesa_ids FROM reservas WHERE date(fecha_reserva) = ? AND estado = 'confirmada' AND mesa_ids IS NOT NULL LIMIT 1");
    $bookedToday->execute([$today]);
    $todayRow = $bookedToday->fetchColumn();
    if ($todayRow) {
        $todayMids = json_decode($todayRow, true);
        $targetMid = $todayMids[0];
        echo "Target mesa_id (booked today): $targetMid\n";

        $conflictOld = $db->prepare("SELECT id FROM reservas WHERE date(fecha_reserva) = ? AND JSON_CONTAINS(mesa_ids, ?) AND estado = ?");
        $conflictOld->execute([$today, '"' . $targetMid . '"', 'confirmada']);
        echo "  OLD (string cast): " . $conflictOld->rowCount() . " conflicts found (BUG: should find existing booking)\n";

        $conflictNew = $db->prepare("SELECT id FROM reservas WHERE date(fecha_reserva) = ? AND JSON_CONTAINS(mesa_ids, ?) AND estado = ?");
        $conflictNew->execute([$today, $targetMid, 'confirmada']);
        echo "  NEW (integer): " . $conflictNew->rowCount() . " conflicts found (FIX: detects the booking)\n";
    } else {
        echo "  No reservations for today ($today) to test against.\n";
    }
} else {
    echo "\nNo existing reservations in DB.\n";
}
echo "\n=== mysql version ===\n";
echo $db->query("SELECT VERSION()")->fetchColumn() . "\n";
