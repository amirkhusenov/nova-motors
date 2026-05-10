<?php
require_once 'config.php';
require_once 'cars_catalog.php';

class Database
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        mysqli_set_charset($this->connection, DB_CHARSET);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function getCarMap(): array
    {
        static $carMap = null;
        if ($carMap !== null) {
            return $carMap;
        }

        $carMap = [];
        foreach (getCarsList() as $car) {
            $carId = (int)($car['id'] ?? 0);
            if ($carId <= 0) {
                continue;
            }

            $carMap[$carId] = [
                'name' => (string)($car['name'] ?? ''),
                'type' => (string)($car['type'] ?? ''),
                'image' => getCarCardImage($car),
                'price' => getCarPriceLabel($car),
            ];
        }

        return $carMap;
    }

    public function userExists($email)
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function loginExists($login)
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function createUser($email, $password, $login = null, $roleId = 1)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO users (email, password, login, role_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $email, $hashedPassword, $login, $roleId);
        return $stmt->execute();
    }

    public function authenticateUser($login, $password)
    {
        $stmt = $this->connection->prepare("SELECT u.id, u.email, u.password, u.login, u.role_id, r.code AS role_code FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $user['is_admin'] = (($user['role_code'] ?? '') === 'admin');
            return $user;
        }
        return false;
    }

    public function getUserById($id)
    {
        $stmt = $this->connection->prepare("SELECT u.id, u.email, u.login, u.first_name, u.last_name, u.phone, u.birth_date, u.address, u.role_id, r.code AS role_code FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateUser($id, $data)
    {
        $allowedFields = ['login', 'email', 'first_name', 'last_name', 'phone', 'birth_date', 'address'];
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields, true)) {
                $updates[] = "$field = ?";
                $types .= "s";
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $types .= "s";
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function deleteUser($id)
    {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function createBooking($car, $customer, $start, $end, $price, $status = 0)
    {
        $stmt = $this->connection->prepare("INSERT INTO rent (car, customer, start, end, price, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdi", $car, $customer, $start, $end, $price, $status);
        if ($stmt->execute()) {
            return $this->connection->insert_id;
        }
        return false;
    }

    public function createPayment($rentId, $amount, $method, $status = 0, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        $stmt = $this->connection->prepare("INSERT INTO payments (rent_id, amount, method, date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idisi", $rentId, $amount, $method, $date, $status);
        return $stmt->execute();
    }

    public function getBookingsByCustomer($customerId)
    {
        $stmt = $this->connection->prepare("SELECT id, car, customer, start, end, price, status FROM rent WHERE customer = ? ORDER BY start DESC");
        $stmt->bind_param("s", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

        $carData = $this->getCarMap();
        foreach ($bookings as &$booking) {
            $carId = (int)($booking['car'] ?? 0);
            if (isset($carData[$carId])) {
                $booking['car_name'] = $carData[$carId]['name'];
                $booking['car_type'] = $carData[$carId]['type'];
                $booking['car_image'] = $carData[$carId]['image'];
            }
        }

        return $bookings;
    }

    public function getBookingById($id)
    {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id WHERE r.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        if ($booking) {
            $carData = $this->getCarMap();
            $carId = (int)($booking['car'] ?? 0);
            if (isset($carData[$carId])) {
                $booking['car_name'] = $carData[$carId]['name'];
                $booking['car_type'] = $carData[$carId]['type'];
                $booking['car_price'] = $carData[$carId]['price'];
            }
        }

        return $booking;
    }

    public function updateBookingStatus($id, $status)
    {
        $stmt = $this->connection->prepare("UPDATE rent SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        return $stmt->execute();
    }

    public function deleteBooking($id)
    {
        $stmt = $this->connection->prepare("DELETE FROM rent WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function isCarAvailable($carId, $start, $end, $excludeBookingId = null)
    {
        $sql = "SELECT COUNT(*) FROM rent WHERE car = ? AND status IN (0, 1) AND ((start <= ? AND end >= ?) OR (start <= ? AND end >= ?) OR (start >= ? AND end <= ?))";
        $params = [$carId, $start, $end, $end, $start, $start, $end];
        $types = "issssss";

        if ($excludeBookingId) {
            $sql .= " AND id != ?";
            $params[] = $excludeBookingId;
            $types .= "i";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0] == 0;
    }

    public function isUserAdmin($userId): bool
    {
        $stmt = $this->connection->prepare("SELECT 1 FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND r.code = 'admin' LIMIT 1");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function getAllBookings()
    {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id ORDER BY r.id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

        $carData = $this->getCarMap();
        foreach ($bookings as &$booking) {
            $carId = (int)($booking['car'] ?? 0);
            if (isset($carData[$carId])) {
                $booking['car_name'] = $carData[$carId]['name'];
                $booking['car_type'] = $carData[$carId]['type'];
                $booking['car_image'] = $carData[$carId]['image'];
            }
        }

        return $bookings;
    }

    public function getPendingBookings()
    {
        $stmt = $this->connection->prepare("SELECT r.*, u.login as customer_name, u.email as customer_email FROM rent r LEFT JOIN users u ON r.customer = u.id WHERE r.status = 0 ORDER BY r.id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

        $carData = $this->getCarMap();
        foreach ($bookings as &$booking) {
            $carId = (int)($booking['car'] ?? 0);
            if (isset($carData[$carId])) {
                $booking['car_name'] = $carData[$carId]['name'];
                $booking['car_type'] = $carData[$carId]['type'];
                $booking['car_image'] = $carData[$carId]['image'];
            }
        }

        return $bookings;
    }
}
?>
