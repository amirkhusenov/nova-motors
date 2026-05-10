<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit();
}

$user = null;
try {
    $db = new Database();
    $user = $db->getUserById($_SESSION['user_id']);
    
    if (!$user) {
        session_unset();
        header('Location: ./login.php');
        exit();
    }
} catch (Exception $e) {
    $error = 'Ошибка загрузки профиля: ' . $e->getMessage();
    session_unset();
    header('Location: ./login.php');
    exit();
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'bookings';

$booking_error = '';
$booking_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    $car_id = (int)$_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_price = (float)$_POST['total_price'];
    
    if (empty($car_id) || empty($start_date) || empty($end_date) || empty($total_price)) {
        $booking_error = 'Все поля обязательны для заполнения';
    } elseif ($start_date >= $end_date) {
        $booking_error = 'Дата окончания должна быть позже даты начала';
    } elseif ($start_date < date('Y-m-d')) {
        $booking_error = 'Дата начала не может быть в прошлом';
    } else {
        try {
            $db = new Database();
            
            if ($db->createBooking($car_id, $_SESSION['user_id'], $start_date, $end_date, $total_price, 0)) {
                $booking_success = 'Заявка на бронирование отправлена! Ожидайте одобрения администратора.';
            } else {
                $booking_error = 'Ошибка при создании заявки';
            }
        } catch (Exception $e) {
            $booking_error = 'Ошибка: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - NOVA MOTORS</title>

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./notifications.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="./notifications.js" defer></script>
</head>

<body class="profile-page">
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="profile-layout">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user['login'] ?: 'Пользователь'); ?></div>
                    <div class="sidebar-user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>

            <div class="sidebar-menu">
                <a href="./profile.php?page=cars" class="menu-item <?php echo $current_page === 'cars' ? 'active' : ''; ?>" data-tooltip="Авто">
                    <i class="bi bi-car-front"></i>
                    <span>Авто</span>
                </a>
                <a href="./profile.php?page=bookings" class="menu-item <?php echo $current_page === 'bookings' ? 'active' : ''; ?>" data-tooltip="Мои бронирования">
                    <i class="bi bi-calendar-check"></i>
                    <span>Мои бронирования</span>
                </a>
            </div>

            <a href="./logout.php" class="menu-item logout" data-tooltip="Выйти">
                <i class="bi bi-box-arrow-right"></i>
                <span>Выйти</span>
            </a>
        </div>

        <div class="main-content">
            <?php if ($current_page === 'cars'): ?>
                <div class="content-header">
                    <h1 class="content-title">Выбор автомобиля</h1>
                </div>

                <div class="content-card">
                    <div class="row">
                        <?php
                        $cars = [
                            [
                                'name' => 'Koenigsegg',
                                'type' => 'Спорт',
                                'image' => 'img/Koenigsegg.jpg',
                                'fuel' => '90L',
                                'transmission' => 'Механика',
                                'seats' => '2 места',
                                'price' => '$99.00'
                            ],
                            [
                                'name' => 'Nissan GT-R',
                                'type' => 'Спорт',
                                'image' => 'img/Nissan GT-R.jpeg',
                                'fuel' => '80L',
                                'transmission' => 'Механика',
                                'seats' => '2 места',
                                'price' => '$80.00'
                            ],
                            [
                                'name' => 'Rolls-Royce',
                                'type' => 'Седан',
                                'image' => 'img/Rolls-Royce.png',
                                'fuel' => '70L',
                                'transmission' => 'Механика',
                                'seats' => '4 места',
                                'price' => '$96.00'
                            ],
                            [
                                'name' => 'MG ZX Excaluce',
                                'type' => 'Хэтчбек',
                                'image' => 'img/MG ZX Excaluce.jpeg',
                                'fuel' => '70L',
                                'transmission' => 'Механика',
                                'seats' => '4 места',
                                'price' => '$76.00'
                            ],
                            [
                                'name' => 'Lada Granta',
                                'type' => 'Седан',
                                'image' => 'img/ladaGranta.webp',
                                'fuel' => '50L',
                                'transmission' => 'Механика',
                                'seats' => '5 мест',
                                'price' => '$45.00'
                            ]
                        ];

                        foreach ($cars as $car): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="car-card">
                                    <div class="car-card-header">
                                        <p class="car-name"><?php echo $car['name']; ?></p>
                                        <p class="car-type"><?php echo $car['type']; ?></p>
                                    </div>
                                    <img src="<?php echo $car['image']; ?>" alt="<?php echo $car['name']; ?>" class="car-image">
                                    <div class="car-specs">
                                        <span><i class="bi bi-fuel-pump"></i> <?php echo $car['fuel']; ?></span>
                                        <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> <?php echo $car['transmission']; ?></span>
                                        <span><i class="bi bi-people"></i> <?php echo $car['seats']; ?></span>
                                    </div>
                                    <div class="car-footer">
                                        <div>
                                            <span class="price"><?php echo $car['price']; ?>/</span><span class="price-period">день</span>
                                        </div>
                                        <button class="rent-btn" onclick="openBookingModal('<?php echo $car['name']; ?>', '<?php echo $car['price']; ?>', '<?php echo $car['image']; ?>')">Забронировать</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php elseif ($current_page === 'bookings'): ?>
                <div class="content-header">
                    <h1 class="content-title">Мои бронирования</h1>
                </div>

                <?php if ($booking_error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($booking_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($booking_success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($booking_success); ?>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <?php
                    try {
                        $bookings = $db->getBookingsByCustomer($_SESSION['user_id']);
                        if (empty($bookings)) { ?>
                            <div class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                <h3>У вас пока нет активных бронирований</h3>
                                <p>Выберите автомобиль и создайте своё первое бронирование</p>
                                <a href="./profile.php?page=cars" class="btn btn-primary-custom mt-3">
                                    Выбрать автомобиль
                                </a>
                            </div>
                        <?php } else { ?>
                            <div class="bookings-list">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="booking-card">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img src="<?php echo htmlspecialchars($booking['car_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($booking['car_name']); ?>" 
                                                     class="booking-car-image">
                                            </div>
                                            <div class="col-md-6">
                                                <h5><?php echo htmlspecialchars($booking['car_name']); ?></h5>
                                                <p class="booking-details">
                                                    <i class="bi bi-calendar-event me-2"></i>
                                                    <?php echo date('d.m.Y', strtotime($booking['start'])); ?> - 
                                                    <?php echo date('d.m.Y', strtotime($booking['end'])); ?>
                                                </p>
                                                <p class="booking-price">
                                                    <i class="bi bi-currency-ruble me-2"></i>
                                                    <?php echo htmlspecialchars($booking['price']); ?> ₽
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="booking-status">
                                                    <?php
                                                    $statusText = '';
                                                    $statusClass = '';
                                                    switch ($booking['status']) {
                                                        case 0:
                                                            $statusText = 'Ожидает одобрения';
                                                            $statusClass = 'warning';
                                                            break;
                                                        case 1:
                                                            $statusText = 'Подтверждено';
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
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php }
                    } catch (Exception $e) { ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Ошибка загрузки бронирований: <?php echo htmlspecialchars($e->getMessage()); ?>
                        </div>
                    <?php } ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Бронирование автомобиля</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="booking-car-info">
                                    <h6>Выбранный автомобиль:</h6>
                                    <div class="selected-car">
                                        <img id="selectedCarImage" src="" alt="" class="selected-car-image">
                                        <div>
                                            <strong id="selectedCarName"></strong>
                                            <div class="car-price-display">
                                                <span id="selectedCarPrice"></span>/день
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="customer-info">
                                    <h6>Информация о клиенте:</h6>
                                    <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['login'] ?: 'Пользователь'); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="startDate" class="form-label">Дата начала аренды</label>
                                    <input type="date" class="form-control" id="startDate" name="startDate" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endDate" class="form-label">Дата окончания аренды</label>
                                    <input type="date" class="form-control" id="endDate" name="endDate" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rentalDays" class="form-label">Количество дней</label>
                                    <input type="number" class="form-control" id="rentalDays" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="totalPrice" class="form-label">Общая стоимость</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="totalPrice" readonly>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Внимание:</strong> Бронирование будет подтверждено после обработки заявки.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="confirmBooking">Подтвердить бронирование</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentCarPrice = 0;
        let currentCarId = 0;
        let currentCarName = '';

        const sidebar = document.getElementById('sidebar');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const menuItems = document.querySelectorAll('.menu-item');

        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
        });

        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
        });

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            }
        });

        function openBookingModal(carName, carPrice, carImage) {
            currentCarName = carName;
            currentCarPrice = parseFloat(carPrice.replace('$', '').replace(',', ''));
            
            document.getElementById('selectedCarName').textContent = carName;
            document.getElementById('selectedCarPrice').textContent = carPrice;
            document.getElementById('selectedCarImage').src = carImage;
            document.getElementById('selectedCarImage').alt = carName;

            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').min = today;
            document.getElementById('endDate').min = today;

            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('rentalDays').value = '';
            document.getElementById('totalPrice').value = '';

            const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
            modal.show();
        }

        function calculateRentalCost() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end > start) {
                    const timeDiff = end.getTime() - start.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                    const totalCost = daysDiff * currentCarPrice;
                    
                    document.getElementById('rentalDays').value = daysDiff;
                    document.getElementById('totalPrice').value = totalCost.toLocaleString('ru-RU');
                } else {
                    document.getElementById('rentalDays').value = '';
                    document.getElementById('totalPrice').value = '';
                }
            }
        }

        document.getElementById('startDate').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('endDate');
            
            if (startDate) {
                endDateInput.min = startDate;
            }
            calculateRentalCost();
        });

        document.getElementById('endDate').addEventListener('change', calculateRentalCost);

        document.getElementById('confirmBooking').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const totalPrice = document.getElementById('totalPrice').value;
            
            if (!startDate || !endDate || !totalPrice) {
                showError('Пожалуйста, заполните все поля и выберите даты');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const fields = [
                {name: 'action', value: 'create_booking'},
                {name: 'car_id', value: getCarIdFromName(currentCarName)},
                {name: 'start_date', value: startDate},
                {name: 'end_date', value: endDate},
                {name: 'total_price', value: totalPrice.replace(/\s/g, '')}
            ];
            
            fields.forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field.name;
                input.value = field.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        });

        function getCarIdFromName(carName) {
            const carMap = {
                'Koenigsegg': 1,
                'Nissan GT-R': 2,
                'Rolls-Royce': 3,
                'MG ZX Excaluce': 4,
                'Lada Granta': 5
            };
            return carMap[carName] || 1;
        }

        // Показываем уведомления на основе PHP переменных
        <?php if (isset($booking_error) && $booking_error): ?>
            showError('<?php echo addslashes($booking_error); ?>');
        <?php endif; ?>
        
        <?php if (isset($booking_success) && $booking_success): ?>
            showSuccess('<?php echo addslashes($booking_success); ?>');
        <?php endif; ?>
    </script>
</body>

</html>
