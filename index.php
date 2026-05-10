<?php
session_start();
require_once 'database.php';
require_once 'cars_catalog.php';

$user = null;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    try {
        $db = new Database();
        $user = $db->getUserById($_SESSION['user_id']);
        
        if (!$user) {
            session_unset();
        }
    } catch (Exception $e) {
        session_unset();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVA MOTORS - Прокат Авто</title>

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        const isUserLoggedIn = <?php echo $user ? 'true' : 'false'; ?>;
    </script>
    <script src="app.js" defer></script>
    <script src="drive_lab.js" defer></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="img/logo.svg" alt="NOVA MOTORS" style="height: 25px;">
            </a>
            <div class="d-flex gap-2">
                <?php if ($user): ?>
                    <a href="./profile.php" class="btn btn-outline-light">
                        <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($user['login'] ?: $user['email']); ?>
                    </a>
                <?php else: ?>
                    <a href="./login.php" class="btn btn-danger">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="hero-content">
            <h1>Арендуйте автомобиль своей мечты</h1>
        </div>
    </section>

    <section id="drive-lab" class="drive-lab-entry">
        <div class="container">
            <div class="drive-lab-shell fade-in">
                <div class="drive-lab-copy">
                    <span class="drive-lab-chip">NOVA DRIVE LAB</span>
                    <h2>&#1042;&#1072;&#1091;-&#1087;&#1086;&#1076;&#1073;&#1086;&#1088; &#1072;&#1074;&#1090;&#1086;: &#1089;&#1094;&#1077;&#1085;&#1072;&#1088;&#1080;&#1081; &#1087;&#1086;&#1077;&#1079;&#1076;&#1082;&#1080; + &#1076;&#1091;&#1101;&#1083;&#1100; &#1084;&#1086;&#1076;&#1077;&#1083;&#1077;&#1081;</h2>
                    <p>&#1053;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081; &#1073;&#1102;&#1076;&#1078;&#1077;&#1090;, &#1082;&#1086;&#1083;&#1080;&#1095;&#1077;&#1089;&#1090;&#1074;&#1086; &#1084;&#1077;&#1089;&#1090; &#1080; &#1087;&#1088;&#1080;&#1086;&#1088;&#1080;&#1090;&#1077;&#1090;&#1099; &#8212; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1072; &#1089;&#1088;&#1072;&#1079;&#1091; &#1074;&#1099;&#1073;&#1077;&#1088;&#1077;&#1090; &#1083;&#1091;&#1095;&#1096;&#1080;&#1077; &#1072;&#1074;&#1090;&#1086; &#1080; &#1087;&#1086;&#1082;&#1072;&#1078;&#1077;&#1090;, &#1082;&#1090;&#1086; &#1087;&#1086;&#1073;&#1077;&#1076;&#1080;&#1090; &#1074; &#1087;&#1088;&#1103;&#1084;&#1086;&#1081; &#1076;&#1091;&#1101;&#1083;&#1080;.</p>
                    <div class="drive-lab-actions">
                        <button type="button" class="btn btn-danger btn-lg open-drive-lab">&#1047;&#1072;&#1087;&#1091;&#1089;&#1090;&#1080;&#1090;&#1100; DRIVE LAB</button>
                        <a href="#park" class="drive-lab-link">&#1055;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088;&#1077;&#1090;&#1100; &#1072;&#1074;&#1090;&#1086;&#1087;&#1072;&#1088;&#1082;</a>
                    </div>
                </div>
                <div class="drive-lab-glow" aria-hidden="true">
                    <span class="dl-ring dl-ring-a"></span>
                    <span class="dl-ring dl-ring-b"></span>
                    <span class="dl-ring dl-ring-c"></span>
                </div>
            </div>
        </div>
    </section>

    <section id="park">
        <?php
        $allCars = getCarsList();
        $popularCars = array_slice($allCars, 0, 8);
        $fleetCars = array_slice($allCars, 8);
        ?>

        <section id="vehicles" class="container">
            <h2 class="section-title fade-in">Популярные автомобили</h2>
            <div class="row">
                <?php foreach ($popularCars as $car): ?>
                    <?php $cardSpecs = getCarCardSpecs($car); ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="car-card fade-in">
                            <div class="car-card-header">
                                <p class="car-name"><?php echo htmlspecialchars($car['name']); ?></p>
                                <p class="car-type"><?php echo htmlspecialchars($car['type']); ?></p>
                            </div>
                            <img src="<?php echo htmlspecialchars(getCarCardImage($car)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="car-image" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                            <div class="car-specs">
                                <span><i class="bi bi-fuel-pump"></i> <?php echo htmlspecialchars($cardSpecs['fuel']); ?></span>
                                <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> <?php echo htmlspecialchars($cardSpecs['transmission']); ?></span>
                                <span><i class="bi bi-people"></i> <?php echo htmlspecialchars($cardSpecs['seats']); ?></span>
                            </div>
                            <div class="car-footer">
                                <div>
                                    <span class="price"><?php echo htmlspecialchars(getCarPriceLabel($car)); ?>/</span><span class="price-period">день</span>
                                </div>
                                <a class="rent-btn" href="./car.php?id=<?php echo (int)$car['id']; ?>">Подробнее</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container">
            <h2 class="section-title fade-in">Наш автопарк</h2>
            <div class="row">
                <?php foreach ($fleetCars as $car): ?>
                    <?php $cardSpecs = getCarCardSpecs($car); ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="car-card fade-in">
                            <div class="car-card-header">
                                <p class="car-name"><?php echo htmlspecialchars($car['name']); ?></p>
                                <p class="car-type"><?php echo htmlspecialchars($car['type']); ?></p>
                            </div>
                            <img src="<?php echo htmlspecialchars(getCarCardImage($car)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="car-image" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                            <div class="car-specs">
                                <span><i class="bi bi-fuel-pump"></i> <?php echo htmlspecialchars($cardSpecs['fuel']); ?></span>
                                <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> <?php echo htmlspecialchars($cardSpecs['transmission']); ?></span>
                                <span><i class="bi bi-people"></i> <?php echo htmlspecialchars($cardSpecs['seats']); ?></span>
                            </div>
                            <div class="car-footer">
                                <div>
                                    <span class="price"><?php echo htmlspecialchars(getCarPriceLabel($car)); ?>/</span><span class="price-period">день</span>
                                </div>
                                <a class="rent-btn" href="./car.php?id=<?php echo (int)$car['id']; ?>">Подробнее</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </section>


    <section class="company-info-section" id="about">
        <div class="container">
            <h2 class="section-title fade-in">О компании NOVA MOTORS</h2>
            <div class="row">
                <div class="col-lg-6">
                    <div class="company-content fade-in-left">
                        <h3>Наша миссия</h3>
                        <p>NOVA MOTORS — это премиальная служба аренды автомобилей, которая предоставляет клиентам доступ к самым современным и надежным автомобилям. Мы стремимся сделать каждую поездку незабываемой, предлагая высокий уровень сервиса и комфорта.</p>
                        
                        <h3>Почему выбирают нас?</h3>
                        <ul class="company-features">
                            <li><i class="bi bi-check-circle-fill"></i> Широкий выбор премиальных автомобилей</li>
                            <li><i class="bi bi-check-circle-fill"></i> Прозрачные цены без скрытых платежей</li>
                            <li><i class="bi bi-check-circle-fill"></i> Круглосуточная поддержка клиентов</li>
                            <li><i class="bi bi-check-circle-fill"></i> Быстрое и простое бронирование</li>
                            <li><i class="bi bi-check-circle-fill"></i> Полная страховка всех автомобилей</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="company-stats fade-in-right">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Довольных клиентов</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Автомобилей в парке</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Поддержка клиентов</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Лет опыта</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <h2 class="section-title fade-in">Часто задаваемые вопросы</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item fade-in">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Какие документы нужны для аренды автомобиля?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Для аренды автомобиля вам понадобятся: водительское удостоверение (стаж вождения не менее 2 лет), паспорт или документ, удостоверяющий личность, и банковская карта для залога.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item fade-in">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Можно ли отменить бронирование?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Да, вы можете отменить бронирование бесплатно за 24 часа до начала аренды. При отмене менее чем за 24 часа взимается штраф в размере 50% от стоимости аренды.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item fade-in">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Включена ли страховка в стоимость?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Да, базовая страховка (КАСКО и ОСАГО) включена в стоимость аренды. Дополнительно вы можете оформить расширенную страховку для полного покрытия.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item fade-in">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Где можно забрать и вернуть автомобиль?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Вы можете забрать и вернуть автомобиль в нашем офисе по адресу: г. Москва, ул. Примерная, д. 123. Также доступна услуга доставки автомобиля к вам (за дополнительную плату).
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item fade-in">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Что делать в случае поломки автомобиля?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    В случае поломки немедленно свяжитесь с нашей службой поддержки по телефону +7 (495) 123-45-67. Мы организуем замену автомобиля или эвакуацию в зависимости от ситуации.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews-section">
        <div class="container">
            <h2 class="section-title fade-in">Отзывы наших клиентов</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="review-card fade-in">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-name">Александр Петров</div>
                                <div class="review-date">15 января 2025</div>
                            </div>
                            <div class="review-rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="review-content">
                            <p>"Отличный сервис! Арендовал Koenigsegg на выходные. Автомобиль в идеальном состоянии, процесс бронирования простой и быстрый. Обязательно обращусь снова!"</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="review-card fade-in">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-name">Мария Сидорова</div>
                                <div class="review-date">12 января 2025</div>
                            </div>
                            <div class="review-rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="review-content">
                            <p>"Первый раз арендовала автомобиль через NOVA MOTORS. Очень довольна! Персонал вежливый, автомобиль чистый, цены адекватные. Рекомендую всем!"</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="review-card fade-in">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-name">Дмитрий Козлов</div>
                                <div class="review-date">8 января 2025</div>
                            </div>
                            <div class="review-rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="review-content">
                            <p>"Брал Rolls-Royce для важной встречи. Впечатления только положительные! Автомобиль превосходный, сервис на высоте. Спасибо за качественную работу!"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 class="fade-in cta-title">Широкий выбор надежных автомобилей</h2>
            <h3 class="fade-in cta-subtitle">Все в одном месте</h3>
            <div class="cta-underline fade-in"></div>
            <div class="cta-buttons">
                <a href="#park" class="fade-in-left btn-cta"><button class="btn-cta-primary">Смотреть автомобили</button></a>
                <button class="btn-cta-secondary fade-in-right">Забронировать</button>
            </div>
        </div>
    </section>

    <div id="driveLabModal" class="drive-lab-modal" hidden>
        <div class="drive-lab-backdrop" data-dl-close></div>
        <div class="drive-lab-dialog" role="dialog" aria-modal="true" aria-labelledby="driveLabTitle">
            <div class="drive-lab-dialog-head">
                <h3 id="driveLabTitle">&#1052;&#1086;&#1076;&#1091;&#1083;&#1100; DRIVE LAB</h3>
                <button type="button" class="drive-lab-close" data-dl-close aria-label="&#1047;&#1072;&#1082;&#1088;&#1099;&#1090;&#1100;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="drive-lab-dialog-body">
                <aside class="drive-lab-controls">
                    <div class="dl-control-group">
                        <label for="dlBudgetRange">&#1041;&#1102;&#1076;&#1078;&#1077;&#1090; &#1074; &#1076;&#1077;&#1085;&#1100;</label>
                        <input id="dlBudgetRange" type="range" min="3000" max="20000" step="100" value="8500">
                        <div id="dlBudgetValue" class="dl-control-value">8.500 &#8381;</div>
                    </div>

                    <div class="dl-control-group">
                        <label for="dlSeatsRange">&#1055;&#1072;&#1089;&#1089;&#1072;&#1078;&#1080;&#1088;&#1086;&#1074;</label>
                        <input id="dlSeatsRange" type="range" min="1" max="7" step="1" value="2">
                        <div id="dlSeatsValue" class="dl-control-value">2</div>
                    </div>

                    <div class="dl-control-group">
                        <label for="dlDaysRange">&#1044;&#1083;&#1080;&#1090;&#1077;&#1083;&#1100;&#1085;&#1086;&#1089;&#1090;&#1100; &#1087;&#1086;&#1077;&#1079;&#1076;&#1082;&#1080;</label>
                        <input id="dlDaysRange" type="range" min="1" max="14" step="1" value="3">
                        <div id="dlDaysValue" class="dl-control-value">3 &#1076;&#1085;&#1103;</div>
                    </div>

                    <div class="dl-control-group">
                        <div class="dl-group-title">&#1057;&#1094;&#1077;&#1085;&#1072;&#1088;&#1080;&#1081; &#1087;&#1086;&#1077;&#1079;&#1076;&#1082;&#1080;</div>
                        <div class="dl-chip-grid" id="dlMoodGroup">
                            <button type="button" class="dl-chip active" data-mood="business">&#1041;&#1080;&#1079;&#1085;&#1077;&#1089;</button>
                            <button type="button" class="dl-chip" data-mood="romance">&#1056;&#1086;&#1084;&#1072;&#1085;&#1090;&#1080;&#1082;&#1072;</button>
                            <button type="button" class="dl-chip" data-mood="family">&#1057;&#1077;&#1084;&#1100;&#1103;</button>
                            <button type="button" class="dl-chip" data-mood="adventure">&#1055;&#1088;&#1080;&#1082;&#1083;&#1102;&#1095;&#1077;&#1085;&#1080;&#1077;</button>
                            <button type="button" class="dl-chip" data-mood="premium">&#1055;&#1088;&#1077;&#1084;&#1080;&#1091;&#1084;</button>
                        </div>
                    </div>

                    <div class="dl-control-group">
                        <div class="dl-group-title">&#1043;&#1083;&#1072;&#1074;&#1085;&#1099;&#1081; &#1087;&#1088;&#1080;&#1086;&#1088;&#1080;&#1090;&#1077;&#1090;</div>
                        <div class="dl-chip-grid" id="dlPriorityGroup">
                            <button type="button" class="dl-chip active" data-priority="comfort">&#1050;&#1086;&#1084;&#1092;&#1086;&#1088;&#1090;</button>
                            <button type="button" class="dl-chip" data-priority="speed">&#1044;&#1088;&#1072;&#1081;&#1074;</button>
                            <button type="button" class="dl-chip" data-priority="economy">&#1069;&#1082;&#1086;&#1085;&#1086;&#1084;&#1080;&#1103;</button>
                            <button type="button" class="dl-chip" data-priority="prestige">&#1055;&#1088;&#1077;&#1089;&#1090;&#1080;&#1078;</button>
                        </div>
                    </div>

                    <div class="dl-control-group">
                        <div class="dl-group-title">&#1058;&#1077;&#1084;&#1087; &#1084;&#1080;&#1089;&#1089;&#1080;&#1080;</div>
                        <div class="dl-chip-grid" id="dlTempoGroup">
                            <button type="button" class="dl-chip active" data-tempo="balanced">&#1041;&#1072;&#1083;&#1072;&#1085;&#1089;</button>
                            <button type="button" class="dl-chip" data-tempo="calm">&#1057;&#1087;&#1086;&#1082;&#1086;&#1081;&#1085;&#1086;</button>
                            <button type="button" class="dl-chip" data-tempo="rush">&#1040;&#1076;&#1088;&#1077;&#1085;&#1072;&#1083;&#1080;&#1085;</button>
                            <button type="button" class="dl-chip" data-tempo="night">&#1053;&#1086;&#1095;&#1085;&#1086;&#1081; &#1088;&#1077;&#1078;&#1080;&#1084;</button>
                        </div>
                    </div>

                    <label class="dl-check">
                        <input id="dlNeed3d" type="checkbox">
                        <span>&#1058;&#1086;&#1083;&#1100;&#1082;&#1086; &#1089; 3D &#1084;&#1086;&#1076;&#1077;&#1083;&#1100;&#1102;</span>
                    </label>

                    <button type="button" id="dlRunButton" class="btn btn-danger w-100">&#1055;&#1077;&#1088;&#1077;&#1089;&#1095;&#1080;&#1090;&#1072;&#1090;&#1100; &#1087;&#1086;&#1076;&#1073;&#1086;&#1088;</button>
                </aside>

                <section class="drive-lab-results">
                    <div class="dl-progress-wrap">
                        <div class="dl-progress-track"><span id="dlProgressBar" class="dl-progress-bar"></span></div>
                        <div id="dlStatusText" class="dl-status-text">&#1043;&#1086;&#1090;&#1086;&#1074;&#1086; &#1082; &#1072;&#1085;&#1072;&#1083;&#1080;&#1079;&#1091;</div>
                    </div>

                    <article class="dl-hero-card">
                        <img id="dlHeroImage" src="" alt="&#1056;&#1077;&#1079;&#1091;&#1083;&#1100;&#1090;&#1072;&#1090; DRIVE LAB">
                        <div class="dl-hero-content">
                            <h4 id="dlHeroName">-</h4>
                            <div id="dlHeroMeta" class="dl-hero-meta">-</div>
                            <ul id="dlHeroReasonList" class="dl-reason-list"></ul>
                            <a id="dlHeroOpen" href="#" class="btn btn-outline-light disabled" tabindex="-1" aria-disabled="true">&#1054;&#1090;&#1082;&#1088;&#1099;&#1090;&#1100; &#1072;&#1074;&#1090;&#1086;</a>
                        </div>
                    </article>

                    <div class="dl-top-grid" id="dlTopList"></div>
                    <div class="dl-duel" id="dlDuel"></div>
                </section>
            </div>
        </div>
    </div>

    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand">NOVA MOTORS</div>
                    <p class="footer-description">Легкое бронирование, элитные автомобили, незабываемые поездки</p>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-legal">
                    <div>©2025 NOVA. Все права защищены</div>
                    <div>
                        <a href="#">Политика конфиденциальности</a>
                        <a href="#">Условия использования</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <?php
    $driveLabCarsSource = isset($allCars) && is_array($allCars) ? $allCars : getCarsList();
    $driveLabCars = array_map(function (array $car): array {
        return [
            'id' => (int)($car['id'] ?? 0),
            'name' => (string)($car['name'] ?? ''),
            'type' => (string)($car['type'] ?? ''),
            'price_per_day' => (float)($car['price_per_day'] ?? 0),
            'fuel' => (string)($car['fuel'] ?? ''),
            'transmission' => (string)($car['transmission'] ?? ''),
            'seats' => (string)($car['seats'] ?? ''),
            'image' => getCarCardImage($car),
            'car_url' => './car.php?id=' . (int)($car['id'] ?? 0),
            'has_3d' => getCar3DModelUrl($car) !== '',
        ];
    }, $driveLabCarsSource);
    ?>
    <script>
        window.NOVA_DRIVE_LAB_CARS = <?php echo json_encode($driveLabCars, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
</body>

</html>




