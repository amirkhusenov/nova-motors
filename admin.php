<?php
session_start();
require_once 'config.php';
require_once 'database.php';
require_once 'cars_catalog.php';
$bookingNotificationsPath = __DIR__ . '/booking_notifications.php';
if (is_file($bookingNotificationsPath)) {
    require_once $bookingNotificationsPath;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    session_unset();
    header('Location: ./login.php');
    exit();
}

$db = new Database();
if (!$db->isUserAdmin($_SESSION['user_id'])) {
    session_unset();
    header('Location: ./login.php');
    exit();
}

$error = '';
$success = '';
$carFormError = '';
$carFormSuccess = '';

$editingCarId = isset($_GET['edit_car_id']) ? (int)$_GET['edit_car_id'] : 0;
$editingCar = $editingCarId > 0 ? getCarById($editingCarId) : null;

function uploadCarPhoto(int $carId, string $fieldName = 'car_photo'): ?string
{
    if ($carId <= 0 || !isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Ошибка загрузки файла');
    }

    $tmpPath = (string)($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException('Некорректный загружаемый файл');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmpPath);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Разрешены только JPG, PNG, WEBP');
    }

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'cars';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Не удалось создать директорию для загрузки');
    }

    $ext = $allowed[$mime];
    $fileName = 'car-' . $carId . '-admin.' . $ext;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        throw new RuntimeException('Не удалось сохранить изображение');
    }

    return 'img/cars/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        try {
            $booking = $db->getBookingById($bookingId);

            if (!$booking) {
                $error = 'Бронирование не найдено';
            } else {
                if ($action === 'approve') {
                    if ($db->updateBookingStatus($bookingId, 1)) {
                        try {
                            if (function_exists('sendBookingApprovalEmail') && function_exists('getCarName') && function_exists('getCarPrice')) {
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
                                $success = 'Бронирование одобрено, письмо отправлено';
                            } else {
                                $success = 'Бронирование одобрено';
                            }
                        } catch (Exception $e) {
                            $success = 'Бронирование одобрено, но письмо не отправлено: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Ошибка при одобрении бронирования';
                    }
                }

                if ($action === 'reject') {
                    if ($db->updateBookingStatus($bookingId, 2)) {
                        try {
                            if (function_exists('sendBookingRejectionEmail') && function_exists('getCarName')) {
                                $carName = getCarName($booking['car']);
                                sendBookingRejectionEmail(
                                    $booking['customer_email'],
                                    $booking['customer_name'],
                                    $carName,
                                    $booking['start'],
                                    $booking['end']
                                );
                                $success = 'Бронирование отклонено, письмо отправлено';
                            } else {
                                $success = 'Бронирование отклонено';
                            }
                        } catch (Exception $e) {
                            $success = 'Бронирование отклонено, но письмо не отправлено: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Ошибка при отклонении бронирования';
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }

    if ($action === 'car_create' || $action === 'car_update') {
        $carPayload = [
            'name' => (string)($_POST['car_name'] ?? ''),
            'year' => (int)($_POST['car_year'] ?? 0),
            'type' => (string)($_POST['car_type'] ?? ''),
            'price_per_day' => (float)($_POST['car_price_per_day'] ?? 0),
            'fuel' => (string)($_POST['car_fuel'] ?? ''),
            'transmission' => (string)($_POST['car_transmission'] ?? ''),
            'seats' => (string)($_POST['car_seats'] ?? ''),
            'description' => (string)($_POST['car_description'] ?? ''),
            'image' => (string)($_POST['car_image'] ?? ''),
        ];

        if ($action === 'car_create') {
            $newCarId = createCatalogCar($carPayload);
            if ($newCarId === false) {
                $carFormError = 'Не удалось создать авто. Проверьте обязательные поля.';
            } else {
                try {
                    $uploadedImagePath = uploadCarPhoto((int)$newCarId);
                    if ($uploadedImagePath !== null) {
                        $carPayload['image'] = $uploadedImagePath;
                        updateCatalogCar((int)$newCarId, $carPayload);
                    }
                } catch (Throwable $e) {
                    $carFormError = 'Авто добавлено, но фото не загружено: ' . $e->getMessage();
                }
                $carFormSuccess = 'Авто добавлено (ID: ' . (int)$newCarId . ')';
            }
        }

        if ($action === 'car_update') {
            $carId = (int)($_POST['car_id'] ?? 0);
            if ($carId <= 0) {
                $carFormError = 'Некорректный ID авто';
            } elseif (!updateCatalogCar($carId, $carPayload)) {
                $carFormError = 'Не удалось обновить авто. Проверьте обязательные поля.';
            } else {
                try {
                    $uploadedImagePath = uploadCarPhoto($carId);
                    if ($uploadedImagePath !== null) {
                        $carPayload['image'] = $uploadedImagePath;
                        updateCatalogCar($carId, $carPayload);
                    }
                } catch (Throwable $e) {
                    $carFormError = 'Авто обновлено, но фото не загружено: ' . $e->getMessage();
                }
                $carFormSuccess = 'Авто обновлено (ID: ' . $carId . ')';
                $editingCarId = 0;
                $editingCar = null;
            }
        }
    }

    if ($action === 'car_delete') {
        $carId = (int)($_POST['car_id'] ?? 0);
        if ($carId <= 0) {
            $carFormError = 'Некорректный ID авто';
        } elseif (!deleteCatalogCar($carId)) {
            $carFormError = 'Не удалось удалить авто';
        } else {
            $carFormSuccess = 'Авто удалено (ID: ' . $carId . ')';
            if ($editingCarId === $carId) {
                $editingCarId = 0;
                $editingCar = null;
            }
        }
    }
}

try {
    $bookings = $db->getAllBookings();
    $cars = getCarsList();
} catch (Exception $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
    $bookings = [];
    $cars = [];
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
                    <h1><i class="bi bi-shield-check me-3"></i>Админ панель</h1>
                </div>
                <div class="col-auto">
                    <div class="admin-user-info"><i class="bi bi-person-circle me-2"></i><span>Администратор</span></div>
                </div>
                <div class="col-auto"><a href="./logout.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-right me-2"></i>Выйти</a></div>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2><i class="bi bi-calendar-check me-2"></i>Управление заявками</h2>
                            <div class="admin-stats">
                                <?php
                                $pendingCount = count(array_filter($bookings, static function ($b) { return (int)$b['status'] === 0; }));
                                $approvedCount = count(array_filter($bookings, static function ($b) { return (int)$b['status'] === 1; }));
                                $rejectedCount = count(array_filter($bookings, static function ($b) { return (int)$b['status'] === 2; }));
                                ?>
                                <span class="stat-badge pending"><i class="bi bi-clock me-1"></i>Ожидают: <?php echo $pendingCount; ?></span>
                                <span class="stat-badge approved"><i class="bi bi-check-circle me-1"></i>Одобрены: <?php echo $approvedCount; ?></span>
                                <span class="stat-badge rejected"><i class="bi bi-x-circle me-1"></i>Отклонены: <?php echo $rejectedCount; ?></span>
                            </div>
                        </div>

                        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

                        <div class="admin-table-container">
                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клиент</th>
                                        <th>Автомобиль</th>
                                        <th>Период</th>
                                        <th>Стоимость</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr class="booking-row status-<?php echo (int)$booking['status']; ?>">
                                            <td><strong>#<?php echo (int)$booking['id']; ?></strong></td>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-name"><?php echo htmlspecialchars($booking['customer_name'] ?? 'Неизвестно'); ?></div>
                                                    <div class="customer-email"><?php echo htmlspecialchars($booking['customer_email'] ?? ''); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="car-info">
                                                    <img src="<?php echo htmlspecialchars($booking['car_image'] ?? ''); ?>" alt="" class="car-thumb">
                                                    <div>
                                                        <div class="car-name"><?php echo htmlspecialchars($booking['car_name'] ?? ''); ?></div>
                                                        <div class="car-type"><?php echo htmlspecialchars($booking['car_type'] ?? ''); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="rental-period">
                                                    <div class="start-date"><i class="bi bi-calendar-event me-1"></i><?php echo date('d.m.Y', strtotime($booking['start'])); ?></div>
                                                    <div class="end-date"><i class="bi bi-calendar-check me-1"></i><?php echo date('d.m.Y', strtotime($booking['end'])); ?></div>
                                                </div>
                                            </td>
                                            <td><span class="price-amount"><?php echo htmlspecialchars($booking['price']); ?> ₽</span></td>
                                            <td>
                                                <?php
                                                $statusText = 'Неизвестно';
                                                $statusClass = 'secondary';
                                                if ((int)$booking['status'] === 0) { $statusText = 'Ожидает'; $statusClass = 'warning'; }
                                                if ((int)$booking['status'] === 1) { $statusText = 'Одобрено'; $statusClass = 'success'; }
                                                if ((int)$booking['status'] === 2) { $statusText = 'Отклонено'; $statusClass = 'danger'; }
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?> status-badge"><?php echo $statusText; ?></span>
                                            </td>
                                            <td>
                                                <?php if ((int)$booking['status'] === 0): ?>
                                                    <div class="action-buttons">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Одобрить эту заявку?')">Одобрить</button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Отклонить эту заявку?')">Отклонить</button>
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
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2><i class="bi bi-car-front me-2"></i>Управление автомобилями</h2>
                        </div>

                        <?php if ($carFormError): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($carFormError); ?></div><?php endif; ?>
                        <?php if ($carFormSuccess): ?><div class="alert alert-success" role="alert"><?php echo htmlspecialchars($carFormSuccess); ?></div><?php endif; ?>

                        <form method="POST" class="mb-4" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $editingCar ? 'car_update' : 'car_create'; ?>">
                            <?php if ($editingCar): ?><input type="hidden" name="car_id" value="<?php echo (int)$editingCar['id']; ?>"><?php endif; ?>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Название</label><input class="form-control" name="car_name" required value="<?php echo htmlspecialchars((string)($editingCar['name'] ?? '')); ?>"></div>
                                <div class="col-md-2"><label class="form-label">Год</label><input class="form-control" type="number" name="car_year" required min="1900" max="2100" value="<?php echo htmlspecialchars((string)($editingCar['year'] ?? '')); ?>"></div>
                                <div class="col-md-3"><label class="form-label">Тип</label><input class="form-control" name="car_type" required value="<?php echo htmlspecialchars((string)($editingCar['type'] ?? '')); ?>"></div>
                                <div class="col-md-3"><label class="form-label">Цена за день</label><input class="form-control" type="number" step="0.01" min="0" name="car_price_per_day" required value="<?php echo htmlspecialchars((string)($editingCar['price_per_day'] ?? '')); ?>"></div>
                                <div class="col-md-3"><label class="form-label">Топливо</label><input class="form-control" name="car_fuel" value="<?php echo htmlspecialchars((string)($editingCar['fuel'] ?? '')); ?>"></div>
                                <div class="col-md-3"><label class="form-label">Коробка передач</label><input class="form-control" name="car_transmission" value="<?php echo htmlspecialchars((string)($editingCar['transmission'] ?? '')); ?>"></div>
                                <div class="col-md-2"><label class="form-label">Мест</label><input class="form-control" name="car_seats" value="<?php echo htmlspecialchars((string)($editingCar['seats'] ?? '')); ?>"></div>
                                <div class="col-md-4"><label class="form-label">Изображение (URL/путь)</label><input class="form-control" name="car_image" value="<?php echo htmlspecialchars((string)($editingCar['image'] ?? '')); ?>"></div>
                                <div class="col-md-4">
                                    <label class="form-label">Фото авто</label>
                                    <input class="form-control" type="file" name="car_photo" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <div class="col-md-12"><label class="form-label">Описание</label><textarea class="form-control" name="car_description" rows="2"><?php echo htmlspecialchars((string)($editingCar['description'] ?? '')); ?></textarea></div>
                                <div class="col-md-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><?php echo $editingCar ? 'Сохранить изменения' : 'Добавить авто'; ?></button>
                                    <?php if ($editingCar): ?><a href="./admin.php" class="btn btn-outline-secondary">Отмена</a><?php endif; ?>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th>Год</th>
                                    <th>Цена/день</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($cars as $car): ?>
                                    <tr>
                                        <td><?php echo (int)($car['id'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars((string)($car['name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($car['type'] ?? '')); ?></td>
                                        <td><?php echo (int)($car['year'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars(getCarPriceLabel($car)); ?></td>
                                        <td>
                                            <div class="action-buttons admin-car-actions">
                                                <form method="GET" action="./admin.php" class="d-inline">
                                                    <input type="hidden" name="edit_car_id" value="<?php echo (int)($car['id'] ?? 0); ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary admin-car-action-btn">Редактировать</button>
                                                </form>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Удалить авто ID <?php echo (int)($car['id'] ?? 0); ?>?');">
                                                    <input type="hidden" name="action" value="car_delete">
                                                    <input type="hidden" name="car_id" value="<?php echo (int)($car['id'] ?? 0); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger admin-car-action-btn">Удалить</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($error): ?>showError('<?php echo addslashes($error); ?>');<?php endif; ?>
    <?php if ($success): ?>showSuccess('<?php echo addslashes($success); ?>');<?php endif; ?>
    <?php if ($carFormError): ?>showError('<?php echo addslashes($carFormError); ?>');<?php endif; ?>
    <?php if ($carFormSuccess): ?>showSuccess('<?php echo addslashes($carFormSuccess); ?>');<?php endif; ?>
});
</script>
</body>
</html>
