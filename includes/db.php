<?php
require_once __DIR__ . '/config.php';

$host = env('DB_HOST', '127.0.0.1');
$dbname = env('DB_NAME', 'chisto_pro39_db');
$user = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');
$port = (int) env('DB_PORT', '3306');

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    $message = app_debug()
        ? 'Ошибка подключения к базе данных: ' . $conn->connect_error
        : 'Временная ошибка подключения к базе данных. Попробуйте позже.';
    die($message);
}

$conn->set_charset('utf8mb4');
?>
