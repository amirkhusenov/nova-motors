<?php
session_start();

require_once 'config.php';
require_once 'database.php';
require_once 'cars_catalog.php';

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$car = getCarById($carId);

if (!$car) {
    header('Location: ./index.php#park');
    exit();
}

$isUserLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user = null;
$bookingError = '';
$bookingSuccess = '';
$availablePaymentMethods = [
    1 => '&#1041;&#1072;&#1085;&#1082;&#1086;&#1074;&#1089;&#1082;&#1072;&#1103; &#1082;&#1072;&#1088;&#1090;&#1072;',
    2 => '&#1053;&#1072;&#1083;&#1080;&#1095;&#1085;&#1099;&#1077;',
    3 => '&#1055;&#1077;&#1088;&#1077;&#1074;&#1086;&#1076; &#1085;&#1072; &#1089;&#1095;&#1077;&#1090;',
];

if ($isUserLoggedIn) {
    try {
        $db = new Database();
        $user = $db->getUserById($_SESSION['user_id']);
        if (!$user) {
            session_unset();
            $isUserLoggedIn = false;
        }
    } catch (Exception $e) {
        $bookingError = '&#1054;&#1096;&#1080;&#1073;&#1082;&#1072; &#1079;&#1072;&#1075;&#1088;&#1091;&#1079;&#1082;&#1080; &#1087;&#1088;&#1086;&#1092;&#1080;&#1083;&#1103;: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_car_booking') {
    if (!$isUserLoggedIn) {
        header('Location: ./login.php');
        exit();
    }

    $startDate = trim((string)($_POST['start_date'] ?? ''));
    $endDate = trim((string)($_POST['end_date'] ?? ''));
    $paymentMethod = isset($_POST['payment_method']) ? (int)$_POST['payment_method'] : 0;
    $totalPrice = (float)preg_replace('/[^\d.]/', '', (string)($_POST['total_price'] ?? '0'));

    if ($startDate === '' || $endDate === '' || $totalPrice <= 0) {
        $bookingError = '&#1047;&#1072;&#1087;&#1086;&#1083;&#1085;&#1080;&#1090;&#1077; &#1076;&#1072;&#1090;&#1099; &#1072;&#1088;&#1077;&#1085;&#1076;&#1099;.';
    } elseif ($startDate >= $endDate) {
        $bookingError = '&#1044;&#1072;&#1090;&#1072; &#1086;&#1082;&#1086;&#1085;&#1095;&#1072;&#1085;&#1080;&#1103; &#1076;&#1086;&#1083;&#1078;&#1085;&#1072; &#1073;&#1099;&#1090;&#1100; &#1087;&#1086;&#1079;&#1078;&#1077; &#1076;&#1072;&#1090;&#1099; &#1085;&#1072;&#1095;&#1072;&#1083;&#1072;.';
    } elseif ($startDate < date('Y-m-d')) {
        $bookingError = '&#1044;&#1072;&#1090;&#1072; &#1085;&#1072;&#1095;&#1072;&#1083;&#1072; &#1085;&#1077; &#1084;&#1086;&#1078;&#1077;&#1090; &#1073;&#1099;&#1090;&#1100; &#1074; &#1087;&#1088;&#1086;&#1096;&#1083;&#1086;&#1084;.';
    } elseif (!array_key_exists($paymentMethod, $availablePaymentMethods)) {
        $bookingError = '&#1042;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; &#1089;&#1087;&#1086;&#1089;&#1086;&#1073; &#1086;&#1087;&#1083;&#1072;&#1090;&#1099;.';
    } else {
        try {
            $db = new Database();
            if (!$db->isCarAvailable($carId, $startDate, $endDate)) {
                $bookingError = '&#1040;&#1074;&#1090;&#1086; &#1091;&#1078;&#1077; &#1079;&#1072;&#1085;&#1103;&#1090;&#1086; &#1085;&#1072; &#1101;&#1090;&#1080; &#1076;&#1072;&#1090;&#1099;.';
            } else {
                $bookingId = $db->createBooking($carId, $_SESSION['user_id'], $startDate, $endDate, $totalPrice, 0);
                if ($bookingId && $db->createPayment($bookingId, $totalPrice, $paymentMethod, 0)) {
                    $bookingSuccess = 'Заявка на бронирование отправлена. Ожидайте одобрения администратора.';
                } else {
                    $bookingError = '&#1053;&#1077; &#1091;&#1076;&#1072;&#1083;&#1086;&#1089;&#1100; &#1089;&#1086;&#1079;&#1076;&#1072;&#1090;&#1100; &#1079;&#1072;&#1103;&#1074;&#1082;&#1091;.';
                }
            }
        } catch (Exception $e) {
            $bookingError = '&#1054;&#1096;&#1080;&#1073;&#1082;&#1072;: ' . $e->getMessage();
        }
    }
}
$userLabel = $_SESSION['user_login'] ?? ($_SESSION['user_email'] ?? 'Профиль');

$userLabel = $user['login'] ?? $user['email'] ?? $userLabel;
$mainImage = getCarCardImage($car);
$gallery = [$mainImage];
$cardSpecs = getCarCardSpecs($car);

$fallbackSpecs = [
    'Класс' => (string)($car['type'] ?? '-'),
    'Год' => (string)($car['year'] ?? '-'),
    'Топливо' => (string)($cardSpecs['fuel'] ?? '-'),
    'Коробка' => (string)($cardSpecs['transmission'] ?? '-'),
    'Мест' => (string)($cardSpecs['seats'] ?? '-'),
    'Цена / день' => getCarPriceLabel($car),
];

$specs = $fallbackSpecs;
$priceLabel = getCarPriceLabel($car);
$model3dUrl = getCar3DModelUrl($car);
$has3dModel = $model3dUrl !== '';
$model3dViewerType = getCar3DViewerType($model3dUrl);
$model3dEmbedUrl = getCar3DEmbedUrl($model3dUrl);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string)$car['name']); ?> - NOVA MOTORS</title>

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./style.css">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
</head>

<body class="car-page">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="./index.php">
                <img src="img/logo.svg" alt="NOVA MOTORS" style="height: 25px;">
            </a>
            <div class="d-flex gap-2">
                <?php if ($isUserLoggedIn): ?>
                    <a href="./profile.php" class="btn btn-outline-light">
                        <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars((string)$userLabel); ?>
                    </a>
                <?php else: ?>
                    <a href="./login.php" class="btn btn-danger">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="car-detail-page">
        <div class="container">
            <div class="car-detail-head">
                <a href="./index.php#park" class="car-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Назад к каталогу
                </a>
            </div>

            <div class="car-detail-grid">
                <section class="car-gallery-card">
                    <?php if ($mainImage !== ''): ?>
                        <img id="mainCarImage" class="car-main-image" src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars((string)$car['name']); ?>" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                    <?php else: ?>
                        <div class="car-main-image car-main-image-empty">Изображение не найдено</div>
                    <?php endif; ?>

                    <?php if (count($gallery) > 1): ?>
                        <div class="car-thumbs">
                            <?php foreach ($gallery as $index => $image): ?>
                                <button type="button" class="car-thumb-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($image); ?>">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars((string)$car['name']); ?> photo <?php echo $index + 1; ?>" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="car-info-card">
                    <div class="car-type-badge"><?php echo htmlspecialchars((string)($car['type'] ?? 'Авто')); ?></div>
                    <h1 class="car-title"><?php echo htmlspecialchars((string)$car['name']); ?></h1>

                    <?php if (!empty($car['description'])): ?>
                        <p class="car-description"><?php echo htmlspecialchars((string)$car['description']); ?></p>
                    <?php endif; ?>

                    <div class="car-price-box">
                        <span class="car-price-value"><?php echo htmlspecialchars($priceLabel); ?></span>
                        <span class="car-price-period">/ день</span>
                    </div>

                    <div class="car-actions">
                        <?php if ($isUserLoggedIn): ?>
                            <a href="#booking-form" class="btn btn-danger">&#1055;&#1077;&#1088;&#1077;&#1081;&#1090;&#1080; &#1082; &#1073;&#1088;&#1086;&#1085;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1080;&#1102;</a>
                        <?php else: ?>
                            <a href="./register.php" class="btn btn-danger">&#1042;&#1086;&#1081;&#1090;&#1080; &#1080; &#1079;&#1072;&#1073;&#1088;&#1086;&#1085;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;</a>
                        <?php endif; ?>
                        <?php if ($has3dModel): ?>
                            <button type="button" class="btn btn-outline-dark js-open-car-3d" data-model-url="<?php echo htmlspecialchars($model3dUrl); ?>" data-model-poster="<?php echo htmlspecialchars($mainImage); ?>" data-viewer-type="<?php echo htmlspecialchars($model3dViewerType); ?>" data-embed-url="<?php echo htmlspecialchars($model3dEmbedUrl); ?>">
                                <i class="bi bi-badge-3d me-1"></i>&#1057;&#1084;&#1086;&#1090;&#1088;&#1077;&#1090;&#1100; 3D &#1084;&#1086;&#1076;&#1077;&#1083;&#1100;
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-dark" disabled title="&#1044;&#1086;&#1073;&#1072;&#1074;&#1100; &#1092;&#1072;&#1081;&#1083; &#1084;&#1086;&#1076;&#1077;&#1083;&#1080;: models/cars/car-<?php echo (int)$car['id']; ?>.glb">
                                <i class="bi bi-badge-3d me-1"></i>3D &#1084;&#1086;&#1076;&#1077;&#1083;&#1100; &#1089;&#1082;&#1086;&#1088;&#1086;
                            </button>
                        <?php endif; ?>
                        <a href="./index.php#park" class="btn btn-outline-secondary">&#1053;&#1072; &#1075;&#1083;&#1072;&#1074;&#1085;&#1091;&#1102;</a>
                    </div>

                </section>
            </div>

            <section class="car-specs-card">
                <div class="car-specs-header">
                    <h2>Характеристики</h2>
                </div>

                <?php if (empty($specs)): ?>
                    <p class="mb-0 text-muted">Характеристики пока недоступны.</p>
                <?php else: ?>
                    <div class="car-specs-list">
                        <?php foreach ($specs as $label => $value): ?>
                            <div class="car-spec-row">
                                <span><?php echo htmlspecialchars((string)$label); ?></span>
                                <strong><?php echo htmlspecialchars((string)$value); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="car-booking-card" id="booking-form">
                <div class="car-booking-header">
                    <div>
                        <span class="car-type-badge">&#1041;&#1088;&#1086;&#1085;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;</span>
                        <h2>&#1047;&#1072;&#1103;&#1074;&#1082;&#1072; &#1085;&#1072; <?php echo htmlspecialchars((string)$car['name']); ?></h2>
                    </div>
                </div>

                <?php if ($bookingError !== ''): ?>
                    <div class="alert alert-danger"><?php echo $bookingError; ?></div>
                <?php endif; ?>

                <?php if ($bookingSuccess !== ''): ?>
                    <div class="alert alert-success"><?php echo $bookingSuccess; ?></div>
                <?php endif; ?>

                <?php if (!$isUserLoggedIn): ?>
                    <div class="booking-login-required">
                        <i class="bi bi-person-lock"></i>
                        <div>
                            <h3>&#1053;&#1091;&#1078;&#1085;&#1072; &#1072;&#1074;&#1090;&#1086;&#1088;&#1080;&#1079;&#1072;&#1094;&#1080;&#1103;</h3>
                            <p>&#1042;&#1086;&#1081;&#1076;&#1080;&#1090;&#1077; &#1074; &#1072;&#1082;&#1082;&#1072;&#1091;&#1085;&#1090;, &#1095;&#1090;&#1086;&#1073;&#1099; &#1086;&#1090;&#1087;&#1088;&#1072;&#1074;&#1080;&#1090;&#1100; &#1079;&#1072;&#1103;&#1074;&#1082;&#1091; &#1085;&#1072; &#1073;&#1088;&#1086;&#1085;&#1100;.</p>
                            <a href="./login.php" class="btn btn-danger">&#1042;&#1086;&#1081;&#1090;&#1080;</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form class="car-booking-form" id="carBookingForm" method="POST" novalidate>
                        <input type="hidden" name="action" value="create_car_booking">
                        <input type="hidden" name="total_price" id="bookingTotalRaw" value="">

                        <div class="booking-summary">
                            <div class="booking-form-grid">
                                <div class="booking-panel">
                                    <h3>&#1044;&#1072;&#1090;&#1099; &#1080; &#1086;&#1087;&#1083;&#1072;&#1090;&#1072;</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="bookingStartDate" class="form-label">&#1044;&#1072;&#1090;&#1072; &#1085;&#1072;&#1095;&#1072;&#1083;&#1072;</label>
                                            <input type="date" class="form-control" id="bookingStartDate" name="start_date" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bookingEndDate" class="form-label">&#1044;&#1072;&#1090;&#1072; &#1086;&#1082;&#1086;&#1085;&#1095;&#1072;&#1085;&#1080;&#1103;</label>
                                            <input type="date" class="form-control" id="bookingEndDate" name="end_date" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bookingDays" class="form-label">&#1044;&#1085;&#1077;&#1081;</label>
                                            <input type="number" class="form-control" id="bookingDays" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bookingTotal" class="form-label">&#1048;&#1090;&#1086;&#1075;&#1086;</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="bookingTotal" readonly>
                                                <span class="input-group-text">&#8381;</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="bookingPaymentMethod" class="form-label">&#1057;&#1087;&#1086;&#1089;&#1086;&#1073; &#1086;&#1087;&#1083;&#1072;&#1090;&#1099;</label>
                                            <select class="form-select" id="bookingPaymentMethod" name="payment_method" required>
                                                <option value="">&#1042;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; &#1089;&#1087;&#1086;&#1089;&#1086;&#1073;</option>
                                                <?php foreach ($availablePaymentMethods as $methodId => $methodName): ?>
                                                    <option value="<?php echo (int)$methodId; ?>"><?php echo $methodName; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-price-note">
                                <strong><?php echo htmlspecialchars($priceLabel); ?></strong>
                                <span>/ &#1076;&#1077;&#1085;&#1100;</span>
                            </div>
                            <div class="booking-submit-row">
                                <button type="submit" class="btn btn-danger btn-lg" id="submitBookingBtn" disabled>
                                    &#1054;&#1090;&#1087;&#1088;&#1072;&#1074;&#1080;&#1090;&#1100; &#1079;&#1072;&#1103;&#1074;&#1082;&#1091;
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div class="car-3d-modal" id="car3dModal" hidden>
        <div class="car-3d-backdrop" data-close-car-3d></div>
        <div class="car-3d-dialog" role="dialog" aria-modal="true" aria-labelledby="car3dTitle">
            <div class="car-3d-header">
                <h3 id="car3dTitle">3D &#1084;&#1086;&#1076;&#1077;&#1083;&#1100;: <?php echo htmlspecialchars((string)$car['name']); ?></h3>
                <button type="button" class="car-3d-close" data-close-car-3d aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="car-3d-viewer-wrap">
                <model-viewer
                    id="car3dViewer"
                    class="car-3d-viewer"
                    camera-controls
                    auto-rotate
                    shadow-intensity="1"
                    exposure="1"
                    interaction-prompt="none"
                    loading="eager"
                    alt="3D модель автомобиля">
                </model-viewer>
                <iframe id="car3dEmbed" class="car-3d-embed" src="about:blank" title="Просмотр 3D модели" frameborder="0" allow="autoplay; fullscreen; xr-spatial-tracking" allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>
            </div>
        </div>
    </div>
    <script>
        const mainCarImage = document.getElementById('mainCarImage');
        const thumbButtons = document.querySelectorAll('.car-thumb-btn');
        const openCar3dButton = document.querySelector('.js-open-car-3d');
        const car3dModal = document.getElementById('car3dModal');
        const car3dViewer = document.getElementById('car3dViewer');
        const car3dEmbed = document.getElementById('car3dEmbed');
        const closeCar3dButtons = document.querySelectorAll('[data-close-car-3d]');
        const carDailyPrice = <?php echo json_encode((float)($car['price_per_day'] ?? 0)); ?>;

        const notifyError = (message) => {
            if (typeof showError === 'function') {
                showError(message);
            } else {
                alert(message);
            }
        };

        const bookingForm = document.getElementById('carBookingForm');
        const startDateInput = document.getElementById('bookingStartDate');
        const endDateInput = document.getElementById('bookingEndDate');
        const bookingDaysInput = document.getElementById('bookingDays');
        const bookingTotalInput = document.getElementById('bookingTotal');
        const bookingTotalRawInput = document.getElementById('bookingTotalRaw');
        const submitBookingBtn = document.getElementById('submitBookingBtn');
        const bookingPaymentMethodInput = document.getElementById('bookingPaymentMethod');

        const updateSubmitButtonState = () => {
            if (!submitBookingBtn) return;
            const hasTotal = bookingTotalRawInput && bookingTotalRawInput.value;
            const hasPayment = bookingPaymentMethodInput && bookingPaymentMethodInput.value;
            submitBookingBtn.disabled = !(hasTotal && hasPayment);
        };

        const calculateBookingTotal = () => {
            if (!startDateInput || !endDateInput || !bookingDaysInput || !bookingTotalInput || !bookingTotalRawInput) return;
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            if (!startDate || !endDate) {
                bookingDaysInput.value = '';
                bookingTotalInput.value = '';
                bookingTotalRawInput.value = '';
                updateSubmitButtonState();
                return;
            }

            const start = new Date(startDate);
            const end = new Date(endDate);
            if (end <= start) {
                bookingDaysInput.value = '';
                bookingTotalInput.value = '';
                bookingTotalRawInput.value = '';
                updateSubmitButtonState();
                return;
            }

            const days = Math.ceil((end.getTime() - start.getTime()) / (1000 * 3600 * 24));
            const total = days * carDailyPrice;
            bookingDaysInput.value = days;
            bookingTotalInput.value = total.toLocaleString('ru-RU');
            bookingTotalRawInput.value = total.toFixed(2);
            updateSubmitButtonState();
        };

        if (startDateInput && endDateInput) {
            const today = new Date().toISOString().split('T')[0];
            startDateInput.min = today;
            endDateInput.min = today;
            startDateInput.addEventListener('change', () => {
                endDateInput.min = startDateInput.value || today;
                calculateBookingTotal();
            });
            endDateInput.addEventListener('change', calculateBookingTotal);
        }

        if (bookingPaymentMethodInput) {
            bookingPaymentMethodInput.addEventListener('change', updateSubmitButtonState);
        }

        if (bookingForm) {
            bookingForm.addEventListener('submit', (event) => {
                calculateBookingTotal();
                if (!bookingTotalRawInput.value) {
                    event.preventDefault();
                    notifyError('Выберите корректные даты аренды.');
                    return;
                }
            });
        }

        if (mainCarImage && thumbButtons.length > 0) {
            thumbButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const image = button.dataset.image;
                    mainCarImage.src = image;

                    thumbButtons.forEach((item) => item.classList.remove('active'));
                    button.classList.add('active');
                });
            });
        }

        if (openCar3dButton && car3dModal && car3dViewer && car3dEmbed) {
            const openCar3dModal = () => {
                const modelUrl = openCar3dButton.dataset.modelUrl || '';
                const posterUrl = openCar3dButton.dataset.modelPoster || '';
                const viewerType = openCar3dButton.dataset.viewerType || '';
                const embedUrl = openCar3dButton.dataset.embedUrl || '';
                if (!modelUrl && !embedUrl) {
                    return;
                }

                if (viewerType === 'embed' && embedUrl) {
                    car3dViewer.classList.remove('is-active');
                    car3dViewer.removeAttribute('src');
                    car3dEmbed.classList.add('is-active');
                    car3dEmbed.src = embedUrl;
                } else {
                    car3dEmbed.classList.remove('is-active');
                    car3dEmbed.src = 'about:blank';
                    car3dViewer.classList.add('is-active');
                    car3dViewer.setAttribute('src', modelUrl);
                    if (posterUrl) {
                        car3dViewer.setAttribute('poster', posterUrl);
                    }
                }

                car3dModal.hidden = false;
                document.body.classList.add('car-3d-modal-open');
            };

            const closeCar3dModal = () => {
                car3dModal.hidden = true;
                document.body.classList.remove('car-3d-modal-open');
                car3dViewer.removeAttribute('src');
                car3dViewer.classList.remove('is-active');
                car3dEmbed.classList.remove('is-active');
                car3dEmbed.src = 'about:blank';
            };

            openCar3dButton.addEventListener('click', openCar3dModal);
            closeCar3dButtons.forEach((button) => {
                button.addEventListener('click', closeCar3dModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !car3dModal.hidden) {
                    closeCar3dModal();
                }
            });
        }
    </script>
</body>

</html>
