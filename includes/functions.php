<?php
require_once __DIR__ . '/config.php';

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $data;
}

function ensure_session_started() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function csrf_token() {
    ensure_session_started();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    ensure_session_started();
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function is_valid_phone($phone) {
    $normalized = preg_replace('/[^0-9+]/', '', $phone);
    return (bool) preg_match('/^(\+7|7|8)\d{10}$/', $normalized);
}

function upload_file($file_input_name, $upload_dir = '../uploads/') {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_input_name]['tmp_name'];
        $file_name = basename($_FILES[$file_input_name]['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid('', true) . '_' . time() . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        if ($_FILES[$file_input_name]['size'] > 10 * 1024 * 1024) {
            return ['error' => 'Файл слишком большой. Максимум 10 МБ.'];
        }

        $allowed_types = ['image/jpeg', 'image/png', 'video/mp4', 'video/quicktime'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types, true)) {
            return ['error' => 'Недопустимый тип файла. Разрешены только JPG, PNG, MP4, MOV.'];
        }

        if (move_uploaded_file($file_tmp, $destination)) {
            return ['success' => 'uploads/' . $new_file_name];
        }

        return ['error' => 'Ошибка при сохранении файла.'];
    } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
        return ['error' => 'Ошибка загрузки файла: код ' . $_FILES[$file_input_name]['error']];
    }

    return null;
}

function send_notification($to, $subject, $message) {
    if (app_env() === 'production') {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
        ];
        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, implode("\r\n", $headers));
    }

    $log = "To: $to\nSubject: $subject\nMessage: $message\n\n";
    file_put_contents(__DIR__ . '/../mail_log.txt', $log, FILE_APPEND);
    return true;
}

function get_requests($conn) {
    $sql = 'SELECT * FROM requests ORDER BY created_at DESC';
    $result = $conn->query($sql);
    $requests = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    return $requests;
}
?>
