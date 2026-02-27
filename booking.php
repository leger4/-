<?php
require_once 'config.php';

// Получаем ID сеанса
$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if (!$sessionId) {
    header('Location: afisha.php');
    exit;
}

// Получаем данные сеанса с информацией о фильме
$stmt = $pdo->prepare("
    SELECT s.*, m.title, m.poster, m.duration, m.age_rating 
    FROM sessions s 
    JOIN movies m ON s.movie_id = m.id 
    WHERE s.id = ? AND s.is_active = 1
");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    header('Location: afisha.php');
    exit;
}

$pageTitle = 'Бронирование билетов — ' . $session['title'];

// Получаем забронированные места для этого сеанса
$bookedStmt = $pdo->prepare("SELECT seats FROM bookings WHERE session_id = ? AND status != 'cancelled'");
$bookedStmt->execute([$sessionId]);
$bookings = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

$bookedSeats = [];
foreach ($bookings as $booking) {
    $seats = json_decode($booking, true);
    if ($seats) {
        $bookedSeats = array_merge($bookedSeats, $seats);
    }
}

include 'includes/header.php';
?>

<style>
.booking-container {
    padding: calc(var(--spacing-xl) + 80px) 0 var(--spacing-xl) 0;
    background: linear-gradient(
        180deg,
        var(--color-secondary) 0%,
        var(--color-primary) 100%
    );
    min-height: 100vh;
}

.booking-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: var(--spacing-xl);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.cinema-hall {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.05) 0%,
        rgba(255, 255, 255, 0.02) 100%
    );
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    backdrop-filter: blur(10px);
}

.screen {
    background: linear-gradient(135deg, var(--color-accent) 0%, #7c3aed 100%);
    height: 10px;
    border-radius: 50px;
    margin-bottom: var(--spacing-lg);
    position: relative;
    box-shadow: 0 8px 32px rgba(139, 92, 246, 0.6);
}

.screen::after {
    content: 'ЭКРАН';
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    color: var(--color-text-muted);
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 2px;
}

.seats-grid {
    display: grid;
    gap: 12px;
    margin-top: var(--spacing-xl);
}

.row {
    display: flex;
    gap: 12px;
    align-items: center;
}

.row-number {
    width: 30px;
    text-align: center;
    font-weight: 600;
    color: var(--color-text-muted);
    font-size: 0.9rem;
}

.seat {
    width: 45px;
    height: 45px;
    border-radius: var(--radius-sm);
    border: 2px solid var(--color-border);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    color: var(--color-text-muted);
}

.seat:hover:not(.booked):not(.selected) {
    background: rgba(139, 92, 246, 0.2);
    border-color: var(--color-accent);
    transform: scale(1.1);
}

.seat.selected {
    background: linear-gradient(135deg, var(--color-accent) 0%, #7c3aed 100%);
    border-color: var(--color-accent);
    color: white;
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.5);
}

.seat.booked {
    background: rgba(239, 68, 68, 0.2);
    border-color: #ef4444;
    cursor: not-allowed;
    opacity: 0.5;
}

.legend {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--color-border);
    justify-content: center;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.9rem;
}

.legend-box {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    border: 2px solid;
}

.legend-available {
    border-color: var(--color-border);
    background: rgba(255, 255, 255, 0.05);
}

.legend-selected {
    border-color: var(--color-accent);
    background: var(--color-accent);
}

.legend-booked {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.2);
}

.booking-sidebar {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.05) 0%,
        rgba(255, 255, 255, 0.02) 100%
    );
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.movie-info-compact {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--color-border);
}

.movie-poster-small {
    width: 80px;
    height: 120px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 1px solid var(--color-border);
}

.movie-details-compact h3 {
    font-size: 1.1rem;
    margin-bottom: var(--spacing-xs);
    color: var(--color-text);
}

.movie-details-compact p {
    font-size: 0.85rem;
    color: var(--color-text-muted);
    margin-bottom: 4px;
}

.selected-seats-list {
    margin-bottom: var(--spacing-lg);
}

.selected-seats-list h4 {
    font-size: 1rem;
    margin-bottom: var(--spacing-sm);
    color: var(--color-accent);
}

.seat-item {
    padding: var(--spacing-sm);
    background: rgba(139, 92, 246, 0.1);
    border: 1px solid var(--color-accent);
    border-radius: var(--radius-sm);
    margin-bottom: var(--spacing-xs);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.remove-seat {
    cursor: pointer;
    color: #ef4444;
    font-size: 1.2rem;
    padding: 0 var(--spacing-xs);
    transition: var(--transition-fast);
}

.remove-seat:hover {
    transform: scale(1.3);
}

.total-price {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--color-accent);
    text-align: center;
    margin: var(--spacing-md) 0;
    padding: var(--spacing-md);
    background: rgba(139, 92, 246, 0.1);
    border-radius: var(--radius-md);
}

.booking-form {
    margin-top: var(--spacing-lg);
}

.form-group {
    margin-bottom: var(--spacing-md);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--color-text);
    font-weight: 500;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    color: var(--color-text);
    font-size: 0.95rem;
    transition: var(--transition-smooth);
}

.form-input:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

.booking-btn {
    width: 100%;
    padding: 1.2rem;
    background: linear-gradient(135deg, var(--color-accent) 0%, #7c3aed 100%);
    border: 2px solid rgba(139, 92, 246, 0.4);
    border-radius: var(--radius-sm);
    color: var(--color-text);
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: var(--transition-smooth);
    box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
    font-size: 1rem;
}

.booking-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(139, 92, 246, 0.6);
}

.booking-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-selection {
    text-align: center;
    padding: var(--spacing-lg);
    color: var(--color-text-muted);
}

@media (max-width: 1200px) {
    .booking-grid {
        grid-template-columns: 1fr;
    }
    
    .booking-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .seat {
        width: 35px;
        height: 35px;
        font-size: 0.65rem;
    }
    
    .row {
        gap: 8px;
    }
    
    .seats-grid {
        gap: 8px;
    }
}
</style>

<div class="booking-container">
    <div class="container">
        <h1 style="font-family: var(--font-display); font-size: 3rem; text-align: center; margin-bottom: 3rem; background: linear-gradient(180deg, #ffffff 0%, var(--color-text-secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Выбор мест
        </h1>
        
        <div class="booking-grid">
            <!-- Cinema Hall -->
            <div class="cinema-hall">
                <h3 style="font-size: 1.3rem; margin-bottom: var(--spacing-md); color: var(--color-accent);">
                    <?= h($session['hall']) ?>
                </h3>
                
                <div class="screen"></div>
                
                <div class="seats-grid" id="seatsGrid"></div>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-box legend-available"></div>
                        <span>Свободно</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box legend-selected"></div>
                        <span>Выбрано</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box legend-booked"></div>
                        <span>Занято</span>
                    </div>
                </div>
            </div>
            
            <!-- Booking Sidebar -->
            <div class="booking-sidebar">
                <div class="movie-info-compact">
                    <img src="<?= h($session['poster']) ?>" alt="<?= h($session['title']) ?>" class="movie-poster-small">
                    <div class="movie-details-compact">
                        <h3><?= h($session['title']) ?></h3>
                        <p><?= formatDate($session['session_date']) ?></p>
                        <p>Время: <?= formatTime($session['session_time']) ?></p>
                        <p>Цена: <?= number_format($session['price'], 0, '', ' ') ?> ₽</p>
                    </div>
                </div>
                
                <div class="selected-seats-list">
                    <h4>Выбранные места</h4>
                    <div id="selectedSeatsList" class="empty-selection">
                        Выберите места на схеме зала
                    </div>
                </div>
                
                <div class="total-price" id="totalPrice">0 ₽</div>
                
                <form action="process_booking.php" method="POST" class="booking-form" id="bookingForm">
                    <input type="hidden" name="session_id" value="<?= $sessionId ?>">
                    <input type="hidden" name="seats" id="seatsInput">
                    <input type="hidden" name="total_price" id="totalPriceInput">
                    
                    <div class="form-group">
                        <label class="form-label">Ваше имя</label>
                        <input type="text" name="customer_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <input type="tel" name="customer_phone" class="form-input" required placeholder="+7 (___) ___-__-__">
                    </div>
                    
                    <button type="submit" class="booking-btn" id="bookingBtn" disabled>
                        Забронировать билеты
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const seatPrice = <?= $session['price'] ?>;
const totalSeats = <?= $session['total_seats'] ?>;
const bookedSeats = <?= json_encode($bookedSeats) ?>;
const selectedSeats = [];

// Генерация сетки мест (10 рядов для стандартного зала)
const rows = Math.ceil(totalSeats / 10);
const seatsPerRow = 10;

function generateSeats() {
    const seatsGrid = document.getElementById('seatsGrid');
    let seatNumber = 1;
    
    for (let row = 1; row <= rows; row++) {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';
        
        const rowNumber = document.createElement('div');
        rowNumber.className = 'row-number';
        rowNumber.textContent = row;
        rowDiv.appendChild(rowNumber);
        
        for (let col = 1; col <= seatsPerRow; col++) {
            if (seatNumber > totalSeats) break;
            
            const seat = document.createElement('div');
            seat.className = 'seat';
            const seatId = `${row}-${col}`;
            seat.dataset.seatId = seatId;
            seat.dataset.seatNumber = seatNumber;
            seat.textContent = col;
            
            if (bookedSeats.includes(seatId)) {
                seat.classList.add('booked');
            } else {
                seat.addEventListener('click', () => toggleSeat(seatId, seat));
            }
            
            rowDiv.appendChild(seat);
            seatNumber++;
        }
        
        seatsGrid.appendChild(rowDiv);
    }
}

function toggleSeat(seatId, element) {
    if (element.classList.contains('selected')) {
        element.classList.remove('selected');
        const index = selectedSeats.indexOf(seatId);
        if (index > -1) {
            selectedSeats.splice(index, 1);
        }
    } else {
        element.classList.add('selected');
        selectedSeats.push(seatId);
    }
    
    updateBookingSummary();
}

function updateBookingSummary() {
    const totalPrice = selectedSeats.length * seatPrice;
    const listDiv = document.getElementById('selectedSeatsList');
    const bookingBtn = document.getElementById('bookingBtn');
    
    document.getElementById('totalPrice').textContent = totalPrice.toLocaleString('ru-RU') + ' ₽';
    document.getElementById('totalPriceInput').value = totalPrice;
    document.getElementById('seatsInput').value = JSON.stringify(selectedSeats);
    
    if (selectedSeats.length === 0) {
        listDiv.innerHTML = '<div class="empty-selection">Выберите места на схеме зала</div>';
        bookingBtn.disabled = true;
    } else {
        let html = '';
        selectedSeats.forEach(seat => {
            const [row, col] = seat.split('-');
            html += `
                <div class="seat-item">
                    <span>Ряд ${row}, Место ${col}</span>
                    <span class="remove-seat" onclick="removeSeat('${seat}')">×</span>
                </div>
            `;
        });
        listDiv.innerHTML = html;
        bookingBtn.disabled = false;
    }
}

function removeSeat(seatId) {
    const seat = document.querySelector(`[data-seat-id="${seatId}"]`);
    if (seat) {
        seat.classList.remove('selected');
        const index = selectedSeats.indexOf(seatId);
        if (index > -1) {
            selectedSeats.splice(index, 1);
        }
        updateBookingSummary();
    }
}

// Инициализация
generateSeats();
</script>

<?php include 'includes/footer.php'; ?>
