<?php
require_once 'config.php';
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if (!$sessionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid session ID']);
    exit;
}

// Получаем все бронирования для сеанса
$stmt = $pdo->prepare("
    SELECT b.*, s.price
    FROM bookings b
    JOIN sessions s ON b.session_id = s.id
    WHERE b.session_id = ? AND b.status != 'cancelled'
    ORDER BY b.created_at DESC
");
$stmt->execute([$sessionId]);
$bookings = $stmt->fetchAll();

$bookedSeats = [];
$bookingsMap = [];
$totalRevenue = 0;

foreach ($bookings as $booking) {
    $seats = json_decode($booking['seats'], true);
    if ($seats && is_array($seats)) {
        foreach ($seats as $seat) {
            $bookedSeats[] = $seat;
            $bookingsMap[$seat] = [
                'booking_code' => $booking['booking_code'],
                'customer_name' => $booking['customer_name'],
                'customer_email' => $booking['customer_email'],
                'customer_phone' => $booking['customer_phone'],
                'created_at' => $booking['created_at'],
                'status' => $booking['status']
            ];
        }
    }
    $totalRevenue += $booking['total_price'];
}

header('Content-Type: application/json');
echo json_encode([
    'bookedSeats' => $bookedSeats,
    'bookedCount' => count($bookedSeats),
    'bookingsMap' => $bookingsMap,
    'revenue' => $totalRevenue
]);
?>
