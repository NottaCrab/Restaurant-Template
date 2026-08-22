<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel.php');
    exit;
}

$reservaId = (int) ($_POST['reserva_id'] ?? 0);
$fecha = $_POST['fecha'] ?? date('Y-m-d');

$stmt = $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = :id");
$stmt->execute(['id' => $reservaId]);

header('Location: ../panel.php?fecha=' . urlencode($fecha));
exit;
