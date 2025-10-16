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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } else {
        try {
            $db = new Database();
            
            $user = $db->authenticateUser($login, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['login_time'] = time();
                
                if (isset($user['is_admin']) && $user['is_admin'] === true) {
                    header('Location: ./admin.php');
                } else {
                    header('Location: ./profile.php');
                }
                exit();
            } else {
                $error = 'Неверный логин или пароль';
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
    <title>Вход - NOVA MOTORS</title>
    
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
                            <h1>Вход</h1>
                            <p>Войдите в свой аккаунт</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин</label>
                                <input type="text" class="form-control" id="login" name="login" 
                                       value="<?php echo htmlspecialchars($login ?? ''); ?>" 
                                       placeholder="Введите ваш логин" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Введите пароль" required>
                            </div>
                            
                            <div class="mb-4 remember-me">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Запомнить меня
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none">Забыли пароль?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-auth w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </button>
                        </form>
                        
                        <div class="auth-links">
                            <p>Нет аккаунта? <a href="./register.php">Зарегистрироваться</a></p>
                            <p><a href="./index.php">← Вернуться на главную</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="./notifications.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./notifications.js"></script>
    
    <script>
        // Показываем уведомления на основе PHP переменных
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($error) && $error): ?>
                showError('<?php echo addslashes($error); ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>
