<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$respuesta = ['ok' => false, 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $respuesta['mensaje'] = 'Método no permitido.';
    echo json_encode($respuesta);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$personas = (int) ($_POST['personas'] ?? 0);
$fecha = trim($_POST['fecha'] ?? '');
$hora = (int) ($_POST['hora'] ?? 0);

// --- Validación básica de los 5 campos ---
if ($nombre === '' || $telefono === '' || $personas <= 0 || $fecha === '') {
    $respuesta['mensaje'] = 'Revisa el nombre, teléfono, número de personas, fecha y hora.';
    echo json_encode($respuesta);
    exit;
}

// La fecha debe tener formato correcto (YYYY-MM-DD) y no ser del pasado
$fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
$hoy = new DateTime('today');
if (!$fechaValida || $fechaValida < $hoy) {
    $respuesta['mensaje'] = 'La fecha no es válida.';
    echo json_encode($respuesta);
    exit;
}

// La hora debe estar dentro del horario de reservas disponible
if (!in_array($hora, $horasDisponibles, true)) {
    $respuesta['mensaje'] = 'Esa hora no está disponible para reservar.';
    echo json_encode($respuesta);
    exit;
}

// Si la fecha es HOY, la hora no puede ser ya pasada
if ($fecha === $hoy->format('Y-m-d') && $hora < (int) date('G')) {
    $respuesta['mensaje'] = 'Esa hora ya ha pasado hoy, elige una hora posterior.';
    echo json_encode($respuesta);
    exit;
}

// --- Comprobación de reserva activa por teléfono ---
// Pasamos $fechaHoy y $horaActual desde PHP para compatibilidad total con SQLite
$fechaHoy = date('Y-m-d');
$horaActual = (int) date('G');

$stmtTelefono = $pdo->prepare("
    SELECT fecha, hora
    FROM reservas
    WHERE telefono = :telefono
      AND estado = 'activa'
      AND (
          fecha > :fecha_hoy 
          OR (fecha = :fecha_hoy AND hora >= :hora_actual)
      )
    LIMIT 1
");
$stmtTelefono->execute([
    'telefono' => $telefono,
    'fecha_hoy' => $fechaHoy,
    'hora_actual' => $horaActual,
]);
$reservaExistente = $stmtTelefono->fetch(PDO::FETCH_ASSOC);

if ($reservaExistente) {
    $respuesta['mensaje'] = 'Ya tienes una reserva activa el ' . $reservaExistente['fecha']
        . ' a las ' . $reservaExistente['hora'] . ':00h. No puedes reservar de nuevo hasta que pase esa reserva.';
    echo json_encode($respuesta);
    exit;
}

// --- Buscar la mesa disponible ---
$stmt = $pdo->prepare("
    SELECT id, nombre, asientos
    FROM mesas
    WHERE asientos >= :personas
      AND id NOT IN (
          SELECT mesa_id FROM reservas
          WHERE fecha = :fecha AND hora = :hora AND estado = 'activa'
      )
    ORDER BY asientos ASC
    LIMIT 1
");
$stmt->execute([
    'personas' => $personas,
    'fecha' => $fecha,
    'hora' => $hora,
]);
$mesa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mesa) {
    $respuesta['mensaje'] = 'No hay mesas disponibles para ' . $personas . ' personas el ' . $fecha . ' a las ' . $hora . ':00h.';
    echo json_encode($respuesta);
    exit;
}

// --- Guardar la reserva ---
try {
    $stmtReserva = $pdo->prepare("
        INSERT INTO reservas (mesa_id, nombre, telefono, personas, fecha, hora, estado, creado_en)
        VALUES (:mesa_id, :nombre, :telefono, :personas, :fecha, :hora, 'activa', :creado_en)
    ");
    $stmtReserva->execute([
        'mesa_id' => $mesa['id'],
        'nombre' => $nombre,
        'telefono' => $telefono,
        'personas' => $personas,
        'fecha' => $fecha,
        'hora' => $hora,
        'creado_en' => date('Y-m-d H:i:s'),
    ]);

    $respuesta['ok'] = true;
    $respuesta['mesa'] = $mesa['nombre'];
    $respuesta['mensaje'] = '¡Reserva confirmada en ' . $mesa['nombre'] . ' para el ' . $fecha . ' a las ' . $hora . ':00h!';
} catch (Exception $e) {
    $respuesta['mensaje'] = 'No se pudo completar la reserva. Inténtalo de nuevo.';
}

echo json_encode($respuesta);