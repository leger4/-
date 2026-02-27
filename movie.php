<?php
require_once 'config.php';

// Получаем ID фильма
$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$movieId) {
    header('Location: afisha.php');
    exit;
}

// Получаем данные фильма
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? AND is_active = 1");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: afisha.php');
    exit;
}

$pageTitle = $movie['title'];

// Получаем сеансы для фильма (группируем по датам)
$sessionsStmt = $pdo->prepare("
    SELECT * FROM sessions 
    WHERE movie_id = ? AND is_active = 1 AND session_date >= CURDATE() 
    ORDER BY session_date, session_time
");
$sessionsStmt->execute([$movieId]);
$sessions = $sessionsStmt->fetchAll();

// Группируем сеансы по датам
$sessionsByDate = [];
foreach ($sessions as $session) {
    $date = $session['session_date'];
    if (!isset($sessionsByDate[$date])) {
        $sessionsByDate[$date] = [];
    }
    $sessionsByDate[$date][] = $session;
}

// Получаем случайные фильмы для рекомендаций (кроме текущего)
$recommendStmt = $pdo->prepare("SELECT * FROM movies WHERE is_active = 1 AND id != ? ORDER BY RAND() LIMIT 3");
$recommendStmt->execute([$movieId]);
$recommendedMovies = $recommendStmt->fetchAll();

include 'includes/header.php';
?>

    <!-- Movie Details Section -->
    <section class="movie-details">
        <div class="container">
            <div class="movie-details-grid">
                <!-- Poster Column -->
                <div class="movie-details-poster">
                    <img src="<?= h($movie['poster']) ?>" alt="<?= h($movie['title']) ?>">
                    <?php if ($movie['trailer_url']): ?>
                    <a href="<?= h($movie['trailer_url']) ?>" target="_blank" class="trailer-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <span>Смотреть трейлер</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Info Column -->
                <div class="movie-details-info">
                    <h1 class="movie-details-title"><?= h($movie['title']) ?></h1>
                    <?php if ($movie['title_en']): ?>
                    <p class="movie-details-title-en"><?= h($movie['title_en']) ?></p>
                    <?php endif; ?>
                    
                    <div class="movie-details-meta">
                        <div class="meta-item">
                            <span class="meta-label">Жанр</span>
                            <span class="meta-value"><?= h($movie['genre']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Длительность</span>
                            <span class="meta-value"><?= formatDuration($movie['duration']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Возрастной рейтинг</span>
                            <span class="meta-value age-badge"><?= h($movie['age_rating']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Премьера</span>
                            <span class="meta-value"><?= formatDate($movie['release_date']) ?></span>
                        </div>
                    </div>

                    <div class="movie-details-description">
                        <h3>Описание</h3>
                        <p><?= nl2br(h($movie['description'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sessions Section -->
    <section class="sessions-section">
        <div class="container">
            <h2 class="section-title">Расписание сеансов</h2>
            
            <?php if (empty($sessionsByDate)): ?>
                <div class="no-sessions">
                    <p>К сожалению, на данный момент сеансов нет.</p>
                    <p style="margin-top: 1rem; color: var(--color-text-muted);">Скоро появятся новые сеансы. Следите за обновлениями!</p>
                </div>
            <?php else: ?>
                <div class="sessions-grid">
                    <?php foreach ($sessionsByDate as $date => $dateSessions): ?>
                    <div class="session-day">
                        <h3 class="session-date"><?= formatDate($date) ?></h3>
                        <div class="session-times">
                            <?php foreach ($dateSessions as $session): ?>
                            <div class="session-card">
                                <div class="session-time"><?= formatTime($session['session_time']) ?></div>
                                <div class="session-hall"><?= h($session['hall']) ?></div>
                                <div class="session-price"><?= number_format($session['price'], 0, '', ' ') ?> ₽</div>
                                <div class="session-seats">
                                    <?php 
                                    $availablePercent = ($session['available_seats'] / $session['total_seats']) * 100;
                                    $seatsClass = $availablePercent > 50 ? 'seats-available' : ($availablePercent > 20 ? 'seats-medium' : 'seats-low');
                                    ?>
                                    <span class="<?= $seatsClass ?>">
                                        <?= $session['available_seats'] ?> из <?= $session['total_seats'] ?> мест
                                    </span>
                                </div>
                                <a href="booking.php?session_id=<?= $session['id'] ?>" class="session-btn" style="display: block; text-align: center; text-decoration: none;">
                                    Выбрать место
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recommended Movies Section -->
    <?php if (!empty($recommendedMovies)): ?>
    <section class="recommended-section">
        <div class="container">
            <h2 class="section-title">Рекомендуем посмотреть</h2>
            <div class="movies-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                <?php foreach ($recommendedMovies as $recMovie): ?>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="<?= h($recMovie['poster']) ?>" alt="<?= h($recMovie['title']) ?>">
                        <div class="movie-overlay">
                            <a href="movie.php?id=<?= $recMovie['id'] ?>" class="movie-btn">Подробнее</a>
                        </div>
                    </div>
                    <div class="movie-info">
                        <h3 class="movie-title"><?= h($recMovie['title']) ?></h3>
                        <div class="movie-meta">
                            <span class="genre"><?= h($recMovie['genre']) ?></span>
                            <span class="duration"><?= formatDuration($recMovie['duration']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php include 'includes/footer.php'; ?>
