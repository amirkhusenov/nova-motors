<?php
require_once 'booking_notifications.php';

// Тест отправки уведомлений о бронировании
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $customerEmail = $_POST['customer_email'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $carId = (int)($_POST['car_id'] ?? 1);
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+3 days'));
    
    $carName = getCarName($carId);
    $carPrice = getCarPrice($carId);
    
    if ($action === 'approve') {
        $result = sendBookingApprovalEmail($customerEmail, $customerName, $carName, $startDate, $endDate, $carPrice);
        $message = $result ? 'Письмо об одобрении отправлено!' : 'Ошибка отправки письма об одобрении!';
    } elseif ($action === 'reject') {
        $result = sendBookingRejectionEmail($customerEmail, $customerName, $carName, $startDate, $endDate);
        $message = $result ? 'Письмо об отклонении отправлено!' : 'Ошибка отправки письма об отклонении!';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест уведомлений о бронировании</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .approve-btn {
            background-color: #28a745;
        }
        .approve-btn:hover {
            background-color: #218838;
        }
        .reject-btn {
            background-color: #dc3545;
        }
        .reject-btn:hover {
            background-color: #c82333;
        }
        .result {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Тест уведомлений о бронировании</h1>
        
        <?php if (isset($message)): ?>
            <div class="result <?php echo $result ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📋 Информация:</h3>
            <p>Этот тест позволяет проверить отправку email уведомлений при одобрении или отклонении заявок на бронирование автомобилей.</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="customer_email">Email клиента:</label>
                <input type="email" id="customer_email" name="customer_email" required 
                       placeholder="customer@example.com" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? 'test@example.com'); ?>">
            </div>
            
            <div class="form-group">
                <label for="customer_name">Имя клиента:</label>
                <input type="text" id="customer_name" name="customer_name" required 
                       placeholder="Иван Иванов" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? 'Иван Иванов'); ?>">
            </div>
            
            <div class="form-group">
                <label for="car_id">Автомобиль:</label>
                <select id="car_id" name="car_id">
                    <option value="1">Koenigsegg - $99.00</option>
                    <option value="2">Nissan GT-R - $80.00</option>
                    <option value="3">Rolls-Royce - $96.00</option>
                    <option value="4">MG ZX Excaluce - $76.00</option>
                    <option value="5">Lada Granta - $45.00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="start_date">Дата начала:</label>
                <input type="date" id="start_date" name="start_date" 
                       value="<?php echo $_POST['start_date'] ?? date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="end_date">Дата окончания:</label>
                <input type="date" id="end_date" name="end_date" 
                       value="<?php echo $_POST['end_date'] ?? date('Y-m-d', strtotime('+3 days')); ?>">
            </div>
            
            <button type="submit" name="action" value="approve" class="approve-btn">
                ✅ Отправить письмо об одобрении
            </button>
            
            <button type="submit" name="action" value="reject" class="reject-btn">
                ❌ Отправить письмо об отклонении
            </button>
        </form>
        
        <h3>🔧 Как это работает:</h3>
        <ul>
            <li><strong>admin.php</strong> - при одобрении/отклонении заявки автоматически отправляется email</li>
            <li><strong>booking_notifications.php</strong> - содержит функции для отправки уведомлений</li>
            <li>Используется встроенная функция <code>mail()</code> PHP</li>
            <li>Письма отправляются с красивым HTML-форматированием</li>
        </ul>
    </div>
</body>
</html>
