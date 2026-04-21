<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

function redirect_back_with_error($message) {
    $fallback = 'index.php';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $target = $fallback;

    if ($referer !== '' && filter_var($referer, FILTER_VALIDATE_URL)) {
        $parts = parse_url($referer);
        if (($parts['host'] ?? '') === ($_SERVER['HTTP_HOST'] ?? '')) {
            $path = $parts['path'] ?? '';
            if ($path !== '') {
                $target = ltrim($path, '/');
                if ($target === '') {
                    $target = $fallback;
                }
            }
        }
    }

    $separator = str_contains($target, '?') ? '&' : '?';
    header('Location: ' . $target . $separator . 'form_error=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect_back_with_error('Сессия формы устарела. Обновите страницу и попробуйте снова.');
}

$name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
$phone = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
$service_type = isset($_POST['service_type']) ? clean_input($_POST['service_type']) : '';
$message = isset($_POST['message']) ? clean_input($_POST['message']) : '';
$estimated_price = isset($_POST['estimated_price']) ? clean_input($_POST['estimated_price']) : '';
$source = 'site';

if (empty($phone)) {
    redirect_back_with_error('Телефон обязателен для отправки заявки.');
}

if (!is_valid_phone($phone)) {
    redirect_back_with_error('Введите корректный номер телефона.');
}

if (empty($_POST['agree'])) {
    redirect_back_with_error('Нужно согласие на обработку персональных данных.');
}

$file_path = null;
if (isset($_FILES['file'])) {
    $upload_result = upload_file('file', 'uploads/');
    if ($upload_result) {
        if (isset($upload_result['error'])) {
            redirect_back_with_error('Ошибка загрузки файла: ' . $upload_result['error']);
        } elseif (isset($upload_result['success'])) {
            $file_path = $upload_result['success'];
        }
    }
}

$stmt = $conn->prepare('INSERT INTO requests (name, phone, service_type, message, file_path, estimated_price, source) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('sssssss', $name, $phone, $service_type, $message, $file_path, $estimated_price, $source);

if ($stmt->execute()) {
    $to = env('NOTIFY_EMAIL', 'admin@localhost');
    $subject = 'Новая заявка с сайта ChistoPro';
    $body = "Имя: $name\nТелефон: $phone\nТип услуги: $service_type\nПримерная стоимость: $estimated_price\nСообщение: $message\nФайл: $file_path\nИсточник: $source";
    send_notification($to, $subject, $body);

    $stmt->close();
    $conn->close();

    header('Location: thank_you.php');
    exit;
}

$stmt->close();
$conn->close();
redirect_back_with_error('Ошибка при сохранении заявки. Попробуйте ещё раз.');
?>
