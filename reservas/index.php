<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';



$fecha = $_GET['fecha'] ?? date('Y-m-d');
$fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fechaValida) {
    $fecha = date('Y-m-d');
}

// Todas las mesas, ordenadas por capacidad
$mesas = $pdo->query("SELECT id, nombre, asientos FROM mesas ORDER BY asientos ASC, id ASC")
    ->fetchAll(PDO::FETCH_ASSOC);

// Todas las reservas activas de ese día, indexadas por "mesa_id-hora" para acceso rápido
$stmt = $pdo->prepare("
    SELECT id, mesa_id, nombre, telefono, personas, hora
    FROM reservas
    WHERE fecha = :fecha AND estado = 'activa'
");

$stmt->execute(['fecha' => $fecha]);

$reservasPorSlot = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $reservasPorSlot[$r['mesa_id'] . '-' . $r['hora']] = $r;
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de reservas — New York's Grill</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

    <div class="topbar">
        <h1>Panel de reservas — New York's Grill</h1>
    </div>

    <div class="panel-container">

        <form class="fecha-selector" method="GET" action="panel.php">
            <label for="fecha">Ver reservas del día:</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($fecha) ?>"
                onchange="this.form.submit()">
        </form>

        <div class="tabla-scroll">
            <table class="tabla-reservas">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <?php foreach ($mesas as $mesa): ?>
                            <th><?= htmlspecialchars($mesa['nombre']) ?><br><span
                                    class="asientos-th"><?= (int) $mesa['asientos'] ?> pax</span></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horasDisponibles as $hora): ?>
                        <tr>
                            <td class="hora-td"><?= $hora ?>:00</td>
                            <?php foreach ($mesas as $mesa): ?>
                                <?php $reserva = $reservasPorSlot[$mesa['id'] . '-' . $hora] ?? null; ?>
                                <td class="<?= $reserva ? 'ocupada' : 'libre' ?>">
                                    <?php if ($reserva): ?>
                                        <strong><?= htmlspecialchars($reserva['nombre']) ?></strong><br>
                                        <?= htmlspecialchars($reserva['telefono']) ?><br>
                                        <?= (int) $reserva['personas'] ?> pax
                                        <form action="api/liberar.php" method="POST">
                                            <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                                            <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                                            <button type="submit">Cancelar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="libre-label">Libre</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>