<?php
require_once 'config.php';

// Получаем код бронирования
$bookingCode = isset($_GET['code']) ? $_GET['code'] : '';

if (!$bookingCode) {
    header('Location: afisha.php');
    exit;
}

// Получаем данные бронирования
$stmt = $pdo->prepare("
    SELECT b.*, s.session_date, s.session_time, s.hall, m.title, m.poster 
    FROM bookings b 
    JOIN sessions s ON b.session_id = s.id 
    JOIN movies m ON s.movie_id = m.id 
    WHERE b.booking_code = ?
");
$stmt->execute([$bookingCode]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: afisha.php');
    exit;
}

$pageTitle = 'Бронирование подтверждено';

$seats = json_decode($booking['seats'], true);

include 'includes/header.php';
?>

<style>
.success-container {
    padding: calc(var(--spacing-xl) + 80px) 0 var(--spacing-xl) 0;
    background: linear-gradient(
        180deg,
        var(--color-secondary) 0%,
        var(--color-primary) 100%
    );
    min-height: 100vh;
}

.success-card {
    max-width: 700px;
    margin: 0 auto;
    padding: var(--spacing-xl);
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.08) 0%,
        rgba(255, 255, 255, 0.03) 100%
    );
    border: 1px solid var(--color-border-glow);
    border-radius: var(--radius-lg);
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-cinematic);
    text-align: center;
}

.success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto var(--spacing-lg);
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.4);
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.success-icon svg {
    width: 60px;
    height: 60px;
    stroke: white;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
}

.success-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: var(--spacing-md);
    background: linear-gradient(
        180deg,
        #ffffff 0%,
        var(--color-text-secondary) 100%
    );
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.booking-code {
    display: inline-block;
    padding: 1rem 2rem;
    background: rgba(139, 92, 246, 0.2);
    border: 2px solid var(--color-accent);
    border-radius: var(--radius-md);
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 4px;
    color: var(--color-accent);
    margin: var(--spacing-md) 0;
    font-family: 'Courier New', monospace;
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
}

.booking-details {
    margin: var(--spacing-lg) 0;
    padding: var(--spacing-lg);
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--color-border);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: var(--color-text-muted);
    font-size: 0.95rem;
}

.detail-value {
    color: var(--color-text);
    font-weight: 600;
}

.seats-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-sm);
}

.seat-badge {
    padding: 0.5rem 1rem;
    background: rgba(139, 92, 246, 0.2);
    border: 1px solid var(--color-accent);
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    color: var(--color-accent);
}

.success-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
    justify-content: center;
}

.action-btn {
    padding: 1rem 2rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    transition: var(--transition-smooth);
    font-size: 0.9rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-accent) 0%, #7c3aed 100%);
    border: 2px solid rgba(139, 92, 246, 0.4);
    color: var(--color-text);
    box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(139, 92, 246, 0.6);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid var(--color-border);
    color: var(--color-text);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--color-accent);
}

.info-text {
    color: var(--color-text-muted);
    font-size: 0.9rem;
    margin-top: var(--spacing-md);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .success-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}
</style>

<div class="success-container">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <svg viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            
            <h1 class="success-title">Бронирование подтверждено!</h1>
            
            <p style="color: var(--color-text-muted); font-size: 1.1rem; margin-bottom: var(--spacing-md);">
                Ваши билеты успешно забронированы
            </p>
            
            <div class="booking-code"><?= h($booking['booking_code']) ?></div>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="detail-label">Фильм:</span>
                    <span class="detail-value"><?= h($booking['title']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Дата:</span>
                    <span class="detail-value"><?= formatDate($booking['session_date']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Время:</span>
                    <span class="detail-value"><?= formatTime($booking['session_time']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Зал:</span>
                    <span class="detail-value"><?= h($booking['hall']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Имя:</span>
                    <span class="detail-value"><?= h($booking['customer_name']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?= h($booking['customer_email']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Телефон:</span>
                    <span class="detail-value"><?= h($booking['customer_phone']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Места:</span>
                    <div class="seats-list">
                        <?php foreach ($seats as $seat): 
                            list($row, $col) = explode('-', $seat);
                        ?>
                            <span class="seat-badge">Ряд <?= $row ?>, Место <?= $col ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="detail-row" style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 2px solid var(--color-accent);">
                    <span class="detail-label" style="font-size: 1.2rem;">Итого:</span>
                    <span class="detail-value" style="font-size: 1.5rem; color: var(--color-accent);">
                        <?= number_format($booking['total_price'], 0, '', ' ') ?> ₽
                    </span>
                </div>
            </div>
            
            <p class="info-text">
                Подтверждение бронирования отправлено на ваш email: <strong><?= h($booking['customer_email']) ?></strong><br>
                Сохраните код бронирования: <strong><?= h($booking['booking_code']) ?></strong><br>
                Предъявите его при получении билетов в кассе.
            </p>
            
            <div class="success-actions">
                <a href="index.php" class="action-btn btn-primary">На главную</a>
                <a href="afisha.php" class="action-btn btn-secondary">Смотреть афишу</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
