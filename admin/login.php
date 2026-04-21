<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_locked_until'] = $_SESSION['login_locked_until'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Сессия устарела. Обновите страницу.';
    } elseif (time() < (int) $_SESSION['login_locked_until']) {
        $error = 'Слишком много попыток входа. Попробуйте через 10 минут.';
    } else {
        $password = $_POST['password'] ?? '';

        $passwordHash = env('ADMIN_PASSWORD_HASH', '');
        $plainPassword = env('ADMIN_PASSWORD', '');

        $isValid = false;
        if ($passwordHash !== '') {
            $isValid = password_verify($password, $passwordHash);
        } elseif ($plainPassword !== '' && app_env() !== 'production') {
            $isValid = hash_equals($plainPassword, $password);
        }

        if ($isValid) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_login_at'] = time();
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_locked_until'] = 0;
            header('Location: index.php');
            exit;
        }

        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 600;
            $_SESSION['login_attempts'] = 0;
        }
        $error = 'Неверный пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .login-form {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: var(--light-bg);
            border-radius: 8px;
        }
        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark-blue);
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h1>Вход в админ-панель</h1>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Войти</button>
            </form>
        </div>
    </div>
</body>
</html>
