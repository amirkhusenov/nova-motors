<?php
session_start();
require_once 'config.php';
require_once 'database.php';
require_once 'booking_notifications.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['user_id'] !== 'admin') {
    session_unset();
    header('Location: ./login.php');
    exit();
}

$db = new Database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $bookingId = (int)$_POST['booking_id'];
        
        try {
            // Получаем данные о бронировании перед изменением статуса
            $booking = $db->getBookingById($bookingId);
            
            if (!$booking) {
                $error = 'Бронирование не найдено';
            } else {
                switch ($_POST['action']) {
                    case 'approve':
                        if ($db->updateBookingStatus($bookingId, 1)) {
                            // Отправляем email об одобрении
                            try {
                                $carName = getCarName($booking['car']);
                                $carPrice = getCarPrice($booking['car']);
                                sendBookingApprovalEmail(
                                    $booking['customer_email'], 
                                    $booking['customer_name'], 
                                    $carName, 
                                    $booking['start'], 
                                    $booking['end'], 
                                    $carPrice
                                );
                                $success = 'Бронирование одобрено и уведомление отправлено на email';
                            } catch (Exception $e) {
                                $success = 'Бронирование одобрено, но ошибка отправки email: ' . $e->getMessage();
                            }
                        } else {
                            $error = 'Ошибка при одобрении бронирования';
                        }
                        break;
                        
                    case 'reject':
                        if ($db->updateBookingStatus($bookingId, 2)) {
                            // Отправляем email об отклонении
                            try {
                                $carName = getCarName($booking['car']);
                                sendBookingRejectionEmail(
                                    $booking['customer_email'], 
                                    $booking['customer_name'], 
                                    $carName, 
                                    $booking['start'], 
                                    $booking['end']
                                );
                                $success = 'Бронирование отклонено и уведомление отправлено на email';
                            } catch (Exception $e) {
                                $success = 'Бронирование отклонено, но ошибка отправки email: ' . $e->getMessage();
                            }
                        } else {
                            $error = 'Ошибка при отклонении бронирования';
                        }
                        break;
                }
            }
        } catch (Exception $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

try {
    $bookings = $db->getAllBookings();
} catch (Exception $e) {
    $error = 'Ошибка загрузки заявок: ' . $e->getMessage();
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - NOVA MOTORS</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./notifications.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="./notifications.js" defer></script>
</head>

<body class="profile-page">
    <div class="admin-layout">
        <div class="admin-header">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-between">
                    <div class="col admin-title">
                        <h1>
                            <i class="bi bi-shield-check me-3"></i>
                            Админ панель
                        </h1>
                    </div>
                    <div class="col-auto">
                        <div class="admin-user-info">
                            <i class="bi bi-person-circle me-2"></i>
                            <span>Администратор</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="./logout.php" class="btn btn-outline-light">
                            <i class="bi bi-box-arrow-right me-2"></i>Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2><i class="bi bi-calendar-check me-2"></i>Управление заявками</h2>
                                <div class="admin-stats">
                                    <?php
                                    $pendingCount = count(array_filter($bookings, function($b) { return $b['status'] == 0; }));
                                    $approvedCount = count(array_filter($bookings, function($b) { return $b['status'] == 1; }));
                                    $rejectedCount = count(array_filter($bookings, function($b) { return $b['status'] == 2; }));
                                    $totalCount = count($bookings);
                                    ?>
                                    <span class="stat-badge pending">
                                        <i class="bi bi-clock me-1"></i>
                                        Ожидают: <?php echo $pendingCount; ?>
                                    </span>
                                    <span class="stat-badge approved">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Одобрены: <?php echo $approvedCount; ?>
                                    </span>
                                    <span class="stat-badge rejected">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Отклонены: <?php echo $rejectedCount; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>

                            <div class="admin-table-container">
                                <?php if (empty($bookings)): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h3>Нет заявок на бронирование</h3>
                                        <p>Все заявки обработаны</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table admin-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Клиент</th>
                                                    <th>Автомобиль</th>
                                                    <th>Период аренды</th>
                                                    <th>Стоимость</th>
                                                    <th>Статус</th>
                                                    <th>Действия</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bookings as $booking): ?>
                                                    <tr class="booking-row status-<?php echo $booking['status']; ?>">
                                                        <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                                        <td>
                                                            <div class="customer-info">
                                                                <div class="customer-name"><?php echo htmlspecialchars($booking['customer_name'] ?? 'Неизвестно'); ?></div>
                                                                <div class="customer-email"><?php echo htmlspecialchars($booking['customer_email'] ?? ''); ?></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="car-info">
                                                                <img src="<?php echo htmlspecialchars($booking['car_image'] ?? ''); ?>" 
                                                                     alt="<?php echo htmlspecialchars($booking['car_name'] ?? ''); ?>" 
                                                                     class="car-thumb">
                                                                <div>
                                                                    <div class="car-name"><?php echo htmlspecialchars($booking['car_name'] ?? ''); ?></div>
                                                                    <div class="car-type"><?php echo htmlspecialchars($booking['car_type'] ?? ''); ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="rental-period">
                                                                <div class="start-date">
                                                                    <i class="bi bi-calendar-event me-1"></i>
                                                                    <?php echo date('d.m.Y', strtotime($booking['start'])); ?>
                                                                </div>
                                                                <div class="end-date">
                                                                    <i class="bi bi-calendar-check me-1"></i>
                                                                    <?php echo date('d.m.Y', strtotime($booking['end'])); ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="price-info">
                                                                <span class="price-amount"><?php echo htmlspecialchars($booking['price']); ?> ₽</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $statusText = '';
                                                            $statusClass = '';
                                                            switch ($booking['status']) {
                                                                case 0:
                                                                    $statusText = 'Ожидает';
                                                                    $statusClass = 'warning';
                                                                    break;
                                                                case 1:
                                                                    $statusText = 'Одобрено';
                                                                    $statusClass = 'success';
                                                                    break;
                                                                case 2:
                                                                    $statusText = 'Отклонено';
                                                                    $statusClass = 'danger';
                                                                    break;
                                                                default:
                                                                    $statusText = 'Неизвестно';
                                                                    $statusClass = 'secondary';
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?php echo $statusClass; ?> status-badge">
                                                                <?php echo $statusText; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($booking['status'] == 0): ?>
                                                                <div class="action-buttons">
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                        <input type="hidden" name="action" value="approve">
                                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                                onclick="return confirm('Одобрить эту заявку?')">
                                                                            Одобрить
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                        <input type="hidden" name="action" value="reject">
                                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                                onclick="return confirm('Отклонить эту заявку?')">
                                                                            Отклонить
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">Обработано</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Показываем уведомления на основе PHP переменных
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error): ?>
                showError('<?php echo addslashes($error); ?>');
            <?php endif; ?>
            
            <?php if ($success): ?>
                showSuccess('<?php echo addslashes($success); ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>
