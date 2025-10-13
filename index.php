<?php
session_start();
require_once 'database.php';

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

    <section id="park">
        <section id="vehicles" class="container">
            <h2 class="section-title fade-in">Популярные автомобили</h2>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Koenigsegg</p>
                            <p class="car-type">Спорт</p>
                        </div>
                        <img src="img/Koenigsegg.jpg"
                            alt="Koenigsegg" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 90L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 2 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$99.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Nissan GT-R</p>
                            <p class="car-type">Спорт</p>
                        </div>
                        <img src="img/Nissan GT-R.jpeg"
                            alt="Nissan GT-R" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 80L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 2 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$80.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Rolls-Royce</p>
                            <p class="car-type">Седан</p>
                        </div>
                        <img src="img/Rolls-Royce.png"
                            alt="Rolls-Royce" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 70L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 4 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$96.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Nissan GT-R</p>
                            <p class="car-type">Спорт</p>
                        </div>
                        <img src="img/Nissan GT-R.jpeg"
                            alt="Nissan GT-R" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 80L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 2 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$80.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container">
            <h2 class="section-title fade-in">Наш автопарк</h2>
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">MG ZX Excaluce</p>
                            <p class="car-type">Хэтчбек</p>
                        </div>
                        <img src="img/MG ZX Excaluce.jpeg"
                            alt="MG ZX" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 70L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 4 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$76.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Lada Granta</p>
                            <p class="car-type">Седан</p>
                        </div>
                        <img src="img/ladaGranta.webp"
                            alt="Lada Granta" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 50L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 5 мест</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$45.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">MG ZX Excaluce</p>
                            <p class="car-type">Хэтчбек</p>
                        </div>
                        <img src="img/MG ZX Excaluce.jpeg"
                            alt="MG ZX" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 70L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 4 места</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$76.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="car-card fade-in">
                        <div class="car-card-header">
                            <p class="car-name">Lada Granta</p>
                            <p class="car-type">Седан</p>
                        </div>
                        <img src="img/ladaGranta.webp"
                            alt="Lada Granta" class="car-image">
                        <div class="car-specs">
                            <span><i class="bi bi-fuel-pump"></i> 50L</span>
                            <span><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Механика</span>
                            <span><i class="bi bi-people"></i> 5 мест</span>
                        </div>
                        <div class="car-footer">
                            <div>
                                <span class="price">$45.00/</span><span class="price-period">день</span>
                            </div>
                            <button class="rent-btn">Арендовать</button>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </section>

    <section class="cta-section" id="about">
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
</body>

</html>