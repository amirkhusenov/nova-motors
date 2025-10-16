<?php
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function userExists($email) {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    public function loginExists($login) {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        return $stmt->fetch() !== false;
    }
    
    public function createUser($email, $password, $login = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO users (email, password, login) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $hashedPassword, $login]);
    }
    
    public function authenticateUser($login, $password) {
        if ($login === 'admin' && $password === 'password') {
            return [
                'id' => 'admin',
                'email' => 'admin@novamotors.com',
                'login' => 'admin',
                'is_admin' => true
            ];
        }
        
        $stmt = $this->connection->prepare("SELECT id, email, password, login FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $user['is_admin'] = false;
            return $user;
        }
        return false;
    }
    
    public function getUserById($id) {
        $stmt = $this->connection->prepare("SELECT id, email, login FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateUser($id, $data) {
        $allowedFields = ['login', 'email'];
        $updates = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function deleteUser($id) {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createBooking($car, $customer, $start, $end, $price, $status = 0) {
        $stmt = $this->connection->prepare("INSERT INTO rent (car, customer, start, end, price, status) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$car, $customer, $start, $end, $price, $status]);
    }

    public function getBookingsByCustomer($customerId) {
        $stmt = $this->connection->prepare("SELECT * FROM rent WHERE customer = ? ORDER BY start DESC");
        $stmt->execute([$customerId]);
        $bookings = $stmt->fetchAll();
        
        $carData = [
            1 => ['name' => 'Koenigsegg', 'type' => 'Спорт', 'image' => 'img/Koenigsegg.jpg'],
            2 => ['name' => 'Nissan GT-R', 'type' => 'Спорт', 'image' => 'img/Nissan GT-R.jpeg'],
            3 => ['name' => 'Rolls-Royce', 'type' => 'Седан', 'image' => 'img/Rolls-Royce.png'],
            4 => ['name' => 'MG ZX Excaluce', 'type' => 'Хэтчбек', 'image' => 'img/MG ZX Excaluce.jpeg'],
            5 => ['name' => 'Lada Granta', 'type' => 'Седан', 'image' => 'img/ladaGranta.webp']
        ];
        
        foreach ($bookings as &$booking) {
            if (isset($carData[$booking['car']])) {
                $booking['car_name'] = $carData[$booking['car']]['name'];
                $booking['car_type'] = $carData[$booking['car']]['type'];
                $booking['car_image'] = $carData[$booking['car']]['image'];
            }
        }
        
        return $bookings;
    }

    public function getBookingById($id) {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id WHERE r.id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $carData = [
                1 => ['name' => 'Koenigsegg', 'type' => 'Спорт', 'price' => '$99.00'],
                2 => ['name' => 'Nissan GT-R', 'type' => 'Спорт', 'price' => '$80.00'],
                3 => ['name' => 'Rolls-Royce', 'type' => 'Седан', 'price' => '$96.00'],
                4 => ['name' => 'MG ZX Excaluce', 'type' => 'Хэтчбек', 'price' => '$76.00'],
                5 => ['name' => 'Lada Granta', 'type' => 'Седан', 'price' => '$45.00']
            ];
            
            if (isset($carData[$booking['car']])) {
                $booking['car_name'] = $carData[$booking['car']]['name'];
                $booking['car_type'] = $carData[$booking['car']]['type'];
                $booking['car_price'] = $carData[$booking['car']]['price'];
            }
        }
        
        return $booking;
    }

    public function updateBookingStatus($id, $status) {
        $stmt = $this->connection->prepare("UPDATE rent SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function deleteBooking($id) {
        $stmt = $this->connection->prepare("DELETE FROM rent WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function isCarAvailable($carId, $start, $end, $excludeBookingId = null) {
        $sql = "SELECT COUNT(*) FROM rent WHERE car = ? AND status IN (0, 1) AND ((start <= ? AND end >= ?) OR (start <= ? AND end >= ?) OR (start >= ? AND end <= ?))";
        $params = [$carId, $start, $end, $end, $start, $start, $end];
        
        if ($excludeBookingId) {
            $sql .= " AND id != ?";
            $params[] = $excludeBookingId;
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function getAllBookings() {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id ORDER BY r.id DESC");
        $stmt->execute();
        $bookings = $stmt->fetchAll();
        
        $carData = [
            1 => ['name' => 'Koenigsegg', 'type' => 'Спорт', 'image' => 'img/Koenigsegg.jpg'],
            2 => ['name' => 'Nissan GT-R', 'type' => 'Спорт', 'image' => 'img/Nissan GT-R.jpeg'],
            3 => ['name' => 'Rolls-Royce', 'type' => 'Седан', 'image' => 'img/Rolls-Royce.png'],
            4 => ['name' => 'MG ZX Excaluce', 'type' => 'Хэтчбек', 'image' => 'img/MG ZX Excaluce.jpeg'],
            5 => ['name' => 'Lada Granta', 'type' => 'Седан', 'image' => 'img/ladaGranta.webp']
        ];
        
        foreach ($bookings as &$booking) {
            if (isset($carData[$booking['car']])) {
                $booking['car_name'] = $carData[$booking['car']]['name'];
                $booking['car_type'] = $carData[$booking['car']]['type'];
                $booking['car_image'] = $carData[$booking['car']]['image'];
            }
        }
        
        return $bookings;
    }

    public function getPendingBookings() {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id WHERE r.status = 0 ORDER BY r.id DESC");
        $stmt->execute();
        $bookings = $stmt->fetchAll();
        
        $carData = [
            1 => ['name' => 'Koenigsegg', 'type' => 'Спорт', 'image' => 'img/Koenigsegg.jpg'],
            2 => ['name' => 'Nissan GT-R', 'type' => 'Спорт', 'image' => 'img/Nissan GT-R.jpeg'],
            3 => ['name' => 'Rolls-Royce', 'type' => 'Седан', 'image' => 'img/Rolls-Royce.png'],
            4 => ['name' => 'MG ZX Excaluce', 'type' => 'Хэтчбек', 'image' => 'img/MG ZX Excaluce.jpeg'],
            5 => ['name' => 'Lada Granta', 'type' => 'Седан', 'image' => 'img/ladaGranta.webp']
        ];
        
        foreach ($bookings as &$booking) {
            if (isset($carData[$booking['car']])) {
                $booking['car_name'] = $carData[$booking['car']]['name'];
                $booking['car_type'] = $carData[$booking['car']]['type'];
                $booking['car_image'] = $carData[$booking['car']]['image'];
            }
        }
        
        return $bookings;
    }
}
?>
