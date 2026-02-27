<?php
require_once 'config.php';
session_start();

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        
        // Обновляем время последнего входа
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$admin['id']]);
        
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Проверка авторизации
$isLoggedIn = isset($_SESSION['admin_id']);

// Обработка добавления фильма
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $stmt = $pdo->prepare("
        INSERT INTO movies (title, title_en, genre, duration, age_rating, description, poster, trailer_url, release_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['title'],
        $_POST['title_en'],
        $_POST['genre'],
        $_POST['duration'],
        $_POST['age_rating'],
        $_POST['description'],
        $_POST['poster'],
        $_POST['trailer_url'],
        $_POST['release_date']
    ]);
    $success = 'Фильм успешно добавлен!';
}

// Обработка добавления сеанса
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_session'])) {
    $stmt = $pdo->prepare("
        INSERT INTO sessions (movie_id, session_date, session_time, hall, price, available_seats, total_seats) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['movie_id'],
        $_POST['session_date'],
        $_POST['session_time'],
        $_POST['hall'],
        $_POST['price'],
        $_POST['total_seats'],
        $_POST['total_seats']
    ]);
    $success = 'Сеанс успешно добавлен!';
}

// Получаем список всех фильмов для админки
if ($isLoggedIn) {
    $movies = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll();
    
    // Получаем сеансы с информацией о фильмах для просмотра бронирований
    $sessionsWithMovies = $pdo->query("
        SELECT s.*, m.title, m.poster 
        FROM sessions s 
        JOIN movies m ON s.movie_id = m.id 
        WHERE s.session_date >= CURDATE() 
        ORDER BY s.session_date, s.session_time
    ")->fetchAll();
}

$pageTitle = 'Админ-панель';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — LUMIÈRE</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800&family=Cinzel:wght@400;500;600;700;800;900&family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 2rem 4rem;
            min-height: 100vh;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .admin-title {
            font-family: var(--font-display);
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, var(--color-text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-text);
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            color: var(--color-text);
            font-size: 1rem;
            transition: var(--transition-smooth);
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-btn {
            width: 100%;
            padding: 1rem;
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
        }
        
        .form-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(139, 92, 246, 0.6);
        }
        
        .admin-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .admin-nav-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            color: var(--color-text);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        
        .admin-nav-btn:hover,
        .admin-nav-btn.active {
            background: var(--color-accent);
            border-color: var(--color-accent);
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
        }
        
        .admin-section {
            display: none;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
        }
        
        .admin-section.active {
            display: block;
        }
        
        .error-message {
            padding: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            border-radius: var(--radius-sm);
            color: #ef4444;
            margin-bottom: 1rem;
        }
        
        .success-message {
            padding: 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            border-radius: var(--radius-sm);
            color: #10b981;
            margin-bottom: 1rem;
        }
        
        .movies-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        .movies-table th,
        .movies-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }
        
        .movies-table th {
            color: var(--color-accent);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }
        
        .logout-btn {
            position: fixed;
            top: 90px;
            right: 2rem;
            padding: 0.75rem 1.5rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            border-radius: var(--radius-sm);
            color: #ef4444;
            font-weight: 600;
            transition: var(--transition-smooth);
        }
        
        .logout-btn:hover {
            background: #ef4444;
            color: white;
        }
        
        .back-to-site {
            position: fixed;
            top: 90px;
            left: 2rem;
            padding: 0.75rem 1.5rem;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid var(--color-accent);
            border-radius: var(--radius-sm);
            color: var(--color-accent);
            font-weight: 600;
            transition: var(--transition-smooth);
        }
        
        .back-to-site:hover {
            background: var(--color-accent);
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (!$isLoggedIn): ?>
            <!-- Login Form -->
            <div class="admin-header">
                <h1 class="admin-title">Админ-панель</h1>
                <p style="color: var(--color-text-muted);">Авторизуйтесь для доступа к управлению</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= h($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label class="form-label">Логин</label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" name="login" class="form-btn">Войти</button>
            </form>
            
            <p style="text-align: center; margin-top: 2rem; color: var(--color-text-muted);">
                Тестовый доступ: <strong>admin</strong> / <strong>admin123</strong>
            </p>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" class="admin-nav-btn">← На сайт</a>
            </div>
            
        <?php else: ?>
            <!-- Admin Panel -->
            <a href="index.php" class="back-to-site">← На сайт</a>
            <a href="?logout" class="logout-btn">Выйти</a>
            
            <div class="admin-header">
                <h1 class="admin-title">Админ-панель</h1>
                <p style="color: var(--color-text-muted);">Добро пожаловать, <?= h($_SESSION['admin_name']) ?>!</p>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?= h($success) ?></div>
            <?php endif; ?>
            
            <!-- Navigation -->
            <div class="admin-nav">
                <button class="admin-nav-btn active" onclick="showSection('movies')">Добавить фильм</button>
                <button class="admin-nav-btn" onclick="showSection('sessions')">Добавить сеанс</button>
                <button class="admin-nav-btn" onclick="showSection('bookings')">Бронирования</button>
                <button class="admin-nav-btn" onclick="showSection('list')">Список фильмов</button>
            </div>
            
            <!-- Add Movie Section -->
            <div id="movies-section" class="admin-section active">
                <h2 style="margin-bottom: 2rem; font-family: var(--font-display); font-size: 2rem;">Добавить новый фильм</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Название (рус)</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Название (англ)</label>
                        <input type="text" name="title_en" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Жанр</label>
                        <input type="text" name="genre" class="form-input" placeholder="Фантастика, Драма" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Длительность (минуты)</label>
                        <input type="number" name="duration" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Возрастной рейтинг</label>
                        <select name="age_rating" class="form-select" required>
                            <option value="0+">0+</option>
                            <option value="6+">6+</option>
                            <option value="12+">12+</option>
                            <option value="16+">16+</option>
                            <option value="18+">18+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-textarea" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL постера</label>
                        <input type="url" name="poster" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL трейлера (YouTube)</label>
                        <input type="url" name="trailer_url" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Дата премьеры</label>
                        <input type="date" name="release_date" class="form-input" required>
                    </div>
                    <button type="submit" name="add_movie" class="form-btn">Добавить фильм</button>
                </form>
            </div>
            
            <!-- Add Session Section -->
            <div id="sessions-section" class="admin-section">
                <h2 style="margin-bottom: 2rem; font-family: var(--font-display); font-size: 2rem;">Добавить новый сеанс</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Фильм</label>
                        <select name="movie_id" class="form-select" required>
                            <option value="">Выберите фильм</option>
                            <?php foreach ($movies as $movie): ?>
                                <option value="<?= $movie['id'] ?>"><?= h($movie['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Дата сеанса</label>
                        <input type="date" name="session_date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Время сеанса</label>
                        <input type="time" name="session_time" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Зал</label>
                        <input type="text" name="hall" class="form-input" placeholder="IMAX Зал 1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Цена билета (руб)</label>
                        <input type="number" name="price" class="form-input" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Количество мест</label>
                        <input type="number" name="total_seats" class="form-input" value="100" required>
                    </div>
                    <button type="submit" name="add_session" class="form-btn">Добавить сеанс</button>
                </form>
            </div>
            
            <!-- Bookings Section -->
            <div id="bookings-section" class="admin-section">
                <h2 style="margin-bottom: 2rem; font-family: var(--font-display); font-size: 2rem;">Просмотр бронирований</h2>
                
                <div class="form-group">
                    <label class="form-label">Выберите сеанс</label>
                    <select id="sessionSelect" class="form-select" onchange="loadSessionBookings(this.value)">
                        <option value="">-- Выберите сеанс --</option>
                        <?php foreach ($sessionsWithMovies as $sess): ?>
                            <option value="<?= $sess['id'] ?>" 
                                    data-total-seats="<?= $sess['total_seats'] ?>"
                                    data-movie-title="<?= h($sess['title']) ?>">
                                <?= h($sess['title']) ?> — 
                                <?= formatDate($sess['session_date']) ?>, 
                                <?= formatTime($sess['session_time']) ?> — 
                                <?= h($sess['hall']) ?> 
                                (Занято: <?= $sess['total_seats'] - $sess['available_seats'] ?>/<?= $sess['total_seats'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="bookingsContent" style="margin-top: 2rem; display: none;">
                    <div style="background: rgba(255, 255, 255, 0.05); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 2rem;">
                        <h3 style="font-size: 1.3rem; margin-bottom: 1rem; color: var(--color-accent);" id="sessionTitle"></h3>
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.95rem;">
                            <div>
                                <span style="color: var(--color-text-muted);">Всего мест:</span>
                                <strong id="totalSeatsInfo"></strong>
                            </div>
                            <div>
                                <span style="color: var(--color-text-muted);">Занято:</span>
                                <strong style="color: #ef4444;" id="bookedSeatsCount"></strong>
                            </div>
                            <div>
                                <span style="color: var(--color-text-muted);">Свободно:</span>
                                <strong style="color: #10b981;" id="availableSeatsCount"></strong>
                            </div>
                            <div>
                                <span style="color: var(--color-text-muted);">Выручка:</span>
                                <strong style="color: var(--color-accent);" id="revenueInfo"></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.03); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
                        <div style="text-align: center; margin-bottom: 1rem;">
                            <div style="height: 8px; background: linear-gradient(135deg, var(--color-accent), #7c3aed); border-radius: 50px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 16px rgba(139, 92, 246, 0.5);"></div>
                            <p style="margin-top: 0.5rem; color: var(--color-text-muted); font-size: 0.85rem; font-weight: 600; letter-spacing: 2px;">ЭКРАН</p>
                        </div>
                        
                        <div id="hallSeats" style="margin-top: 2rem;"></div>
                        
                        <div style="display: flex; gap: 2rem; justify-content: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border); flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 30px; height: 30px; border: 2px solid var(--color-border); background: rgba(255, 255, 255, 0.05); border-radius: 6px;"></div>
                                <span>Свободно</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 30px; height: 30px; border: 2px solid #ef4444; background: rgba(239, 68, 68, 0.3); border-radius: 6px;"></div>
                                <span>Занято</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal for booking details -->
                <div id="bookingModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.8); z-index: 10000; backdrop-filter: blur(10px);">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%); border: 1px solid var(--color-border-glow); border-radius: var(--radius-lg); padding: 2rem; max-width: 500px; width: 90%; box-shadow: var(--shadow-cinematic);">
                        <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--color-accent); font-family: var(--font-display);">Детали бронирования</h3>
                        <div id="modalContent"></div>
                        <button onclick="closeModal()" style="margin-top: 1.5rem; width: 100%; padding: 0.75rem; background: var(--color-accent); border: none; border-radius: var(--radius-sm); color: white; font-weight: 600; cursor: pointer; transition: var(--transition-smooth);">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Movies List Section -->
            <div id="list-section" class="admin-section">
                <h2 style="margin-bottom: 2rem; font-family: var(--font-display); font-size: 2rem;">Список фильмов</h2>
                <table class="movies-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Жанр</th>
                            <th>Длительность</th>
                            <th>Рейтинг</th>
                            <th>Дата премьеры</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?= $movie['id'] ?></td>
                            <td><?= h($movie['title']) ?></td>
                            <td><?= h($movie['genre']) ?></td>
                            <td><?= formatDuration($movie['duration']) ?></td>
                            <td><?= h($movie['age_rating']) ?></td>
                            <td><?= formatDate($movie['release_date']) ?></td>
                            <td>
                                <span style="color: <?= $movie['is_active'] ? '#10b981' : '#ef4444' ?>;">
                                    <?= $movie['is_active'] ? 'Активен' : 'Скрыт' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.admin-nav-btn').forEach(btn => btn.classList.remove('active'));
            
            // Show selected section
            document.getElementById(section + '-section').classList.add('active');
            event.target.classList.add('active');
        }
        
        let bookingsData = {};
        
        async function loadSessionBookings(sessionId) {
            if (!sessionId) {
                document.getElementById('bookingsContent').style.display = 'none';
                return;
            }
            
            const select = document.getElementById('sessionSelect');
            const option = select.options[select.selectedIndex];
            const totalSeats = parseInt(option.dataset.totalSeats);
            const movieTitle = option.dataset.movieTitle;
            
            try {
                const response = await fetch(`get_bookings.php?session_id=${sessionId}`);
                const data = await response.json();
                
                bookingsData = data.bookingsMap;
                
                // Update info
                document.getElementById('sessionTitle').textContent = movieTitle;
                document.getElementById('totalSeatsInfo').textContent = totalSeats;
                document.getElementById('bookedSeatsCount').textContent = data.bookedCount;
                document.getElementById('availableSeatsCount').textContent = totalSeats - data.bookedCount;
                document.getElementById('revenueInfo').textContent = data.revenue.toLocaleString('ru-RU') + ' ₽';
                
                // Generate hall
                generateHall(totalSeats, data.bookedSeats);
                
                document.getElementById('bookingsContent').style.display = 'block';
            } catch (error) {
                console.error('Ошибка загрузки бронирований:', error);
                alert('Ошибка загрузки данных');
            }
        }
        
        function generateHall(totalSeats, bookedSeats) {
            const hallDiv = document.getElementById('hallSeats');
            hallDiv.innerHTML = '';
            
            const rows = Math.ceil(totalSeats / 10);
            const seatsPerRow = 10;
            let seatNumber = 1;
            
            for (let row = 1; row <= rows; row++) {
                const rowDiv = document.createElement('div');
                rowDiv.style.cssText = 'display: flex; gap: 12px; margin-bottom: 12px; align-items: center;';
                
                const rowLabel = document.createElement('div');
                rowLabel.style.cssText = 'width: 30px; text-align: center; font-weight: 600; color: var(--color-text-muted); font-size: 0.9rem;';
                rowLabel.textContent = row;
                rowDiv.appendChild(rowLabel);
                
                for (let col = 1; col <= seatsPerRow; col++) {
                    if (seatNumber > totalSeats) break;
                    
                    const seatId = `${row}-${col}`;
                    const seat = document.createElement('div');
                    seat.style.cssText = `
                        width: 45px;
                        height: 45px;
                        border-radius: 6px;
                        border: 2px solid;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    `;
                    seat.textContent = col;
                    
                    if (bookedSeats.includes(seatId)) {
                        seat.style.borderColor = '#ef4444';
                        seat.style.background = 'rgba(239, 68, 68, 0.3)';
                        seat.style.color = '#ef4444';
                        seat.dataset.seatId = seatId;
                        seat.onclick = () => showBookingDetails(seatId);
                        seat.onmouseover = () => {
                            seat.style.transform = 'scale(1.1)';
                            seat.style.boxShadow = '0 4px 16px rgba(239, 68, 68, 0.5)';
                        };
                        seat.onmouseout = () => {
                            seat.style.transform = 'scale(1)';
                            seat.style.boxShadow = 'none';
                        };
                    } else {
                        seat.style.borderColor = 'var(--color-border)';
                        seat.style.background = 'rgba(255, 255, 255, 0.05)';
                        seat.style.color = 'var(--color-text-muted)';
                        seat.style.cursor = 'default';
                        seat.style.opacity = '0.6';
                    }
                    
                    rowDiv.appendChild(seat);
                    seatNumber++;
                }
                
                hallDiv.appendChild(rowDiv);
            }
        }
        
        function showBookingDetails(seatId) {
            const booking = bookingsData[seatId];
            if (!booking) return;
            
            const [row, col] = seatId.split('-');
            
            const content = `
                <div style="background: rgba(255, 255, 255, 0.05); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--color-accent); margin-bottom: 0.5rem;">
                        Место: Ряд ${row}, Место ${col}
                    </div>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                        Код бронирования: <strong style="color: var(--color-text); font-family: 'Courier New', monospace;">${booking.booking_code}</strong>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Имя клиента</div>
                        <div style="font-weight: 600;">${booking.customer_name}</div>
                    </div>
                    <div>
                        <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Email</div>
                        <div style="font-weight: 600;">${booking.customer_email}</div>
                    </div>
                    <div>
                        <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Телефон</div>
                        <div style="font-weight: 600;">${booking.customer_phone}</div>
                    </div>
                    <div>
                        <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Дата бронирования</div>
                        <div style="font-weight: 600;">${new Date(booking.created_at).toLocaleString('ru-RU')}</div>
                    </div>
                    <div>
                        <div style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Статус</div>
                        <div style="font-weight: 600; color: ${booking.status === 'confirmed' ? '#10b981' : '#f59e0b'};">
                            ${booking.status === 'confirmed' ? 'Подтверждено' : booking.status === 'pending' ? 'Ожидание' : 'Отменено'}
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('bookingModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }
        
        // Close modal on background click
        document.getElementById('bookingModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
