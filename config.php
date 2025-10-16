<?php
// Настройки базы данных для Timeweb
// Замените эти значения на ваши реальные данные из панели управления Timeweb
define('DB_HOST', 'localhost');  // Обычно localhost на Timeweb
define('DB_NAME', 'co500272_vova');  // Ваша база данных (обычно префикс + имя)
define('DB_USER', 'co500272_root');  // Ваш пользователь БД
define('DB_PASS', 'ваш_пароль_базы_данных');  // Пароль от базы данных
define('DB_CHARSET', 'utf8mb4');
define('PASSWORD_MIN_LENGTH', 6);

// Email настройки для Timeweb (если нужно)
define('SMTP_HOST', 'smtp.timeweb.ru');  // SMTP сервер Timeweb
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@co500272.tw1.ru');  // Ваш email на домене
define('SMTP_PASS', 'пароль_от_email');  // Пароль от email
define('FROM_EMAIL', 'noreply@co500272.tw1.ru'); // от кого отправлять
define('FROM_NAME', 'NOVA MOTORS');  // имя отправителя
?>
