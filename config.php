<?php
/**
 * LUMIÈRE Cinema - Database Configuration
 * Конфигурация подключения к базе данных
 */

// Параметры подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'lumiere_cinema');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Создание подключения к базе данных
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Настройки сайта
define('SITE_URL', 'http://localhost/курсач/ходунов');
define('SITE_NAME', 'LUMIÈRE');

// Функция для безопасного вывода данных
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Функция для проверки авторизации администратора
function isAdmin() {
    session_start();
    return isset($_SESSION['admin_id']);
}

// Функция для форматирования длительности
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours > 0 ? "{$hours} ч {$mins} мин" : "{$mins} мин";
}

// Функция для форматирования даты
function formatDate($date) {
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// Функция для форматирования времени
function formatTime($time) {
    return date('H:i', strtotime($time));
}

// Функция для получения текущей страницы
function getCurrentPage() {
    $script = basename($_SERVER['PHP_SELF']);
    return str_replace('.php', '', $script);
}
?>
