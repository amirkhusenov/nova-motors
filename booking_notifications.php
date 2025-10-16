<?php
// Функции для отправки уведомлений о бронировании

/**
 * Отправка уведомления об одобрении заявки
 */
function sendBookingApprovalEmail($customerEmail, $customerName, $carName, $startDate, $endDate, $price) {
    $subject = "✅ Заявка на бронирование одобрена - NOVA MOTORS";
    
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Заявка одобрена</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
            <h2 style='color: #28a745; text-align: center;'>🎉 Заявка одобрена!</h2>
            
            <p>Здравствуйте, <strong>$customerName</strong>!</p>
            
            <p>Рады сообщить, что ваша заявка на бронирование автомобиля <strong>$carName</strong> была одобрена!</p>
            
            <div style='background-color: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #28a745;'>
                <h3 style='margin-top: 0; color: #28a745;'>📋 Детали бронирования:</h3>
                <p><strong>Автомобиль:</strong> $carName</p>
                <p><strong>Дата начала:</strong> $startDate</p>
                <p><strong>Дата окончания:</strong> $endDate</p>
                <p><strong>Стоимость:</strong> $$price</p>
            </div>
            
            <p>Пожалуйста, свяжитесь с нами для подтверждения получения автомобиля и оплаты.</p>
            
            <p>Спасибо за выбор NOVA MOTORS! 🚗</p>
            
            <hr style='border: none; border-top: 1px solid #dee2e6; margin: 30px 0;'>
            <p style='font-size: 12px; color: #6c757d; text-align: center;'>
                Это письмо отправлено автоматически. Не отвечайте на него.
            </p>
        </div>
    </body>
    </html>";
    
    $headers = "From: NOVA MOTORS <noreply@novamotors.com>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($customerEmail, $subject, $message, $headers);
}

/**
 * Отправка уведомления об отклонении заявки
 */
function sendBookingRejectionEmail($customerEmail, $customerName, $carName, $startDate, $endDate) {
    $subject = "❌ Заявка на бронирование отклонена - NOVA MOTORS";
    
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Заявка отклонена</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
            <h2 style='color: #dc3545; text-align: center;'>😔 Заявка отклонена</h2>
            
            <p>Здравствуйте, <strong>$customerName</strong>!</p>
            
            <p>К сожалению, ваша заявка на бронирование автомобиля <strong>$carName</strong> была отклонена.</p>
            
            <div style='background-color: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                <h3 style='margin-top: 0; color: #dc3545;'>📋 Детали заявки:</h3>
                <p><strong>Автомобиль:</strong> $carName</p>
                <p><strong>Дата начала:</strong> $startDate</p>
                <p><strong>Дата окончания:</strong> $endDate</p>
            </div>
            
            <p>Возможные причины отказа:</p>
            <ul>
                <li>Автомобиль уже забронирован на указанные даты</li>
                <li>Недостаточно документов или информации</li>
                <li>Технические проблемы с автомобилем</li>
            </ul>
            
            <p>Вы можете подать новую заявку на другой автомобиль или другие даты.</p>
            
            <p>Спасибо за понимание!</p>
            
            <hr style='border: none; border-top: 1px solid #dee2e6; margin: 30px 0;'>
            <p style='font-size: 12px; color: #6c757d; text-align: center;'>
                Это письмо отправлено автоматически. Не отвечайте на него.
            </p>
        </div>
    </body>
    </html>";
    
    $headers = "From: NOVA MOTORS <noreply@novamotors.com>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($customerEmail, $subject, $message, $headers);
}

/**
 * Получить название автомобиля по ID
 */
function getCarName($carId) {
    $carData = [
        1 => 'Koenigsegg',
        2 => 'Nissan GT-R',
        3 => 'Rolls-Royce',
        4 => 'MG ZX Excaluce',
        5 => 'Lada Granta'
    ];
    
    return $carData[$carId] ?? 'Неизвестный автомобиль';
}

/**
 * Получить цену автомобиля по ID
 */
function getCarPrice($carId) {
    $carPrices = [
        1 => '99.00',
        2 => '80.00',
        3 => '96.00',
        4 => '76.00',
        5 => '45.00'
    ];
    
    return $carPrices[$carId] ?? '0.00';
}
?>
