<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: afisha.php');
    exit;
}

// Получаем данные из формы
$sessionId = (int)$_POST['session_id'];
$customerName = trim($_POST['customer_name']);
$customerEmail = trim($_POST['customer_email']);
$customerPhone = trim($_POST['customer_phone']);
$seats = $_POST['seats'];
$totalPrice = (float)$_POST['total_price'];

// Валидация
if (!$sessionId || !$customerName || !$customerEmail || !$customerPhone || !$seats || !$totalPrice) {
    die('Ошибка: все поля обязательны для заполнения');
}

// Проверяем существование сеанса
$sessionStmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND is_active = 1");
$sessionStmt->execute([$sessionId]);
$session = $sessionStmt->fetch();

if (!$session) {
    die('Ошибка: сеанс не найден');
}

// Декодируем выбранные места
$selectedSeats = json_decode($seats, true);
if (!$selectedSeats || !is_array($selectedSeats)) {
    die('Ошибка: некорректные данные мест');
}

// Проверяем, не заняты ли выбранные места
$bookedStmt = $pdo->prepare("SELECT seats FROM bookings WHERE session_id = ? AND status != 'cancelled'");
$bookedStmt->execute([$sessionId]);
$bookings = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

$bookedSeats = [];
foreach ($bookings as $booking) {
    $bookedSeatsData = json_decode($booking, true);
    if ($bookedSeatsData) {
        $bookedSeats = array_merge($bookedSeats, $bookedSeatsData);
    }
}

// Проверяем пересечение
$intersection = array_intersect($selectedSeats, $bookedSeats);
if (!empty($intersection)) {
    die('Ошибка: некоторые из выбранных мест уже заняты. Пожалуйста, выберите другие места.');
}

// Генерируем уникальный код бронирования
$bookingCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

// Начинаем транзакцию
try {
    $pdo->beginTransaction();
    
    // Сохраняем бронирование
    $insertStmt = $pdo->prepare("
        INSERT INTO bookings 
        (session_id, customer_name, customer_email, customer_phone, seats, total_price, booking_code, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $insertStmt->execute([
        $sessionId,
        $customerName,
        $customerEmail,
        $customerPhone,
        $seats,
        $totalPrice,
        $bookingCode
    ]);
    
    // Обновляем количество доступных мест
    $updateStmt = $pdo->prepare("
        UPDATE sessions 
        SET available_seats = available_seats - ? 
        WHERE id = ?
    ");
    $updateStmt->execute([count($selectedSeats), $sessionId]);
    
    $pdo->commit();
    
    // Перенаправляем на страницу подтверждения
    header("Location: booking_success.php?code=$bookingCode");
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    die('Ошибка при бронировании: ' . $e->getMessage());
}
?>
