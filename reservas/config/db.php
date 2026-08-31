<?php

// ============================================================
// CONEXIÓN A LA BASE DE DATOS (MySQL)
// ============================================================
// Rellena estos 3 datos con los que te da tu hosting (en Hostinger:
// hPanel > Bases de datos > MySQL).
// ============================================================

$host = $_ENV['DB_HOST'] ?? 'host-proporcionado-por-el-server';
$db   = $_ENV['DB_NAME'] ?? 'nombre_bd';
$user = $_ENV['DB_USER'] ?? 'usuario_bd';
$pass = $_ENV['DB_PASS'] ?? 'password_bd';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos.');
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS mesas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        asientos INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS reservas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mesa_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(30) NOT NULL,
        personas INT NOT NULL,
        fecha DATE NOT NULL,
        hora TINYINT NOT NULL,
        estado VARCHAR(20) NOT NULL DEFAULT 'activa',
        creado_en DATETIME NOT NULL,
        FOREIGN KEY (mesa_id) REFERENCES mesas(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Siembra mesas de ejemplo SOLO la primera vez (si la tabla está vacía).
// Ajusta nombres/capacidades a las mesas reales del restaurante.
// esto lo tengo que mover a otro sitio para que el cliente a quien se lo venda pueda cambiarlo a gusto
$hayMesas = $pdo->query("SELECT COUNT(*) FROM mesas")->fetchColumn();

if ($hayMesas == 0) {
    $mesasIniciales = [
        ['Mesa 1', 2],
        ['Mesa 2', 2],
        ['Mesa 3', 2],
        ['Mesa 4', 4],
        ['Mesa 5', 4],
        ['Mesa 6', 4],
        ['Mesa 7', 6],
        ['Mesa 8', 8],
    ];

    $stmt = $pdo->prepare("INSERT INTO mesas (nombre, asientos) VALUES (?, ?)");
    foreach ($mesasIniciales as $mesa) {
        $stmt->execute($mesa);
    }
}

// Horario de reservas disponible (mesa reservable de hora en hora).
// por ahora es una landing page y esto hay que cambiarlo a mano, cuando el proyecto sea real
// lo incorporaré en el select del index.html
$horasDisponibles = range(13, 23); // 13:00 a 23:00
