<?php
require_once 'includes/db.php';
require_once 'includes/config.php';

if (app_env() === 'production') {
    http_response_code(403);
    exit('Forbidden');
}

echo 'Подключение к БД успешно!';
$conn->close();
?>
