<?php
session_start();
require_once 'config.php';
require_once 'database.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    try {
        $db = new Database();
        $user_check = $db->getUserById($_SESSION['user_id']);
        if ($user_check) {
            if ($_SESSION['user_id'] === 'admin') {
                header('Location: ./admin.php');
            } else {
                header('Location: ./profile.php');
            }
            exit();
        }
    } catch (Exception $e) {
        session_unset();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $login = trim($_POST['login'] ?? '');
    
    if (empty($email) || empty($password) || empty($confirm_password) || empty($login)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } elseif (strlen($login) < 3) {
        $error = 'Логин должен содержать минимум 3 символа';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ0-9_\-\.]+$/u', $login)) {
        $error = 'Логин может содержать только буквы, цифры, дефисы, подчеркивания и точки';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            $db = new Database();
            
            if ($db->userExists($email)) {
                $error = 'Пользователь с таким email уже существует';
            } elseif ($db->loginExists($login)) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                if ($db->createUser($email, $password, $login)) {
                    $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
                } else {
                    $error = 'Ошибка при создании пользователя';
                }
            }
        } catch (Exception $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - NOVA MOTORS</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="auth-card p-5">
                        <div class="auth-header">
                            <h1>Регистрация</h1>
                            <p>Создайте аккаунт для аренды автомобилей</p>
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
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин</label>
                                <input type="text" class="form-control" id="login" name="login" 
                                       value="<?php echo htmlspecialchars($login ?? ''); ?>" 
                                       placeholder="Введите логин" required>
                                <small class="form-text text-muted">Используйте буквы, цифры, дефисы, подчеркивания или точки</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="Введите ваш email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Введите пароль" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Повторите пароль" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-auth w-100">
                                <i class="bi bi-person-plus me-2"></i>Зарегистрироваться
                            </button>
                        </form>
                        
                        <div class="auth-links">
                            <p>Уже есть аккаунт? <a href="./login.php">Войти</a></p>
                            <p><a href="./index.php">← Вернуться на главную</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
