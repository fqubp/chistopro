<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }

    if ($_POST['action'] === 'change_status' && isset($_POST['id'], $_POST['status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        $allowed_statuses = ['new', 'in_progress', 'completed'];
        if (in_array($status, $allowed_statuses, true)) {
            $stmt = $conn->prepare('UPDATE requests SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: index.php');
        exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare('SELECT file_path FROM requests WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['file_path'] && file_exists('../' . $row['file_path'])) {
                unlink('../' . $row['file_path']);
            }
        }
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM requests WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        header('Location: index.php');
        exit;
    }
}

$requests = get_requests($conn);
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Заявки</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .admin-container { padding: 40px 0; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--gray-light); }
        .admin-table th { background-color: var(--dark-blue); color: white; }
        .admin-table tr:hover { background-color: var(--light-bg); }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .status-new { background-color: #ffc107; color: #000; }
        .status-in_progress { background-color: #17a2b8; color: #fff; }
        .status-completed { background-color: #28a745; color: #fff; }
        .file-link { color: var(--accent-blue); text-decoration: underline; }
        .actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .actions form { display: inline; margin: 0; }
        .icon-btn { border: none; background: transparent; cursor: pointer; font-size: 18px; padding: 0 3px; }
        .icon-btn.delete { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container admin-container">
        <div class="admin-header">
            <h1>Заявки с сайта</h1>
            <a href="logout.php" class="btn">Выйти</a>
        </div>

        <?php if (empty($requests)): ?>
            <p>Пока нет заявок.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                        <th>Услуга</th>
                        <th>Стоимость</th>
                        <th>Комментарий</th>
                        <th>Файл</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?php echo (int) $req['id']; ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($req['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($req['name'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($req['phone']); ?></td>
                            <td><?php echo htmlspecialchars($req['service_type'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($req['estimated_price'] ?: '-'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($req['message'] ?: '-')); ?></td>
                            <td>
                                <?php if ($req['file_path']): ?>
                                    <a href="/<?php echo htmlspecialchars($req['file_path']); ?>" target="_blank" class="file-link">Посмотреть</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($req['status']); ?>">
                                    <?php
                                    switch ($req['status']) {
                                        case 'new': echo 'Новая'; break;
                                        case 'in_progress': echo 'В работе'; break;
                                        case 'completed': echo 'Выполнена'; break;
                                        default: echo htmlspecialchars($req['status']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo (int) $req['id']; ?>" title="Редактировать">✏️</a>

                                <?php foreach (['new' => '📄', 'in_progress' => '⚙️', 'completed' => '✅'] as $statusKey => $icon): ?>
                                    <form method="post" action="index.php">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="id" value="<?php echo (int) $req['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $statusKey; ?>">
                                        <button class="icon-btn" type="submit" title="Статус: <?php echo $statusKey; ?>"><?php echo $icon; ?></button>
                                    </form>
                                <?php endforeach; ?>

                                <form method="post" action="index.php" onsubmit="return confirm('Удалить заявку?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int) $req['id']; ?>">
                                    <button class="icon-btn delete" type="submit" title="Удалить">❌</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
