<?php
require_once 'config.php';

$pageTitle = 'Афиша';

// Получаем жанр из GET-параметра для фильтрации
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';

// Получаем все уникальные жанры для фильтра
$genresStmt = $pdo->query("SELECT DISTINCT genre FROM movies WHERE is_active = 1 ORDER BY genre");
$genres = $genresStmt->fetchAll(PDO::FETCH_COLUMN);

// Формируем запрос с учётом фильтрации
if ($selectedGenre) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE is_active = 1 AND genre LIKE ? ORDER BY release_date DESC");
    $stmt->execute(['%' . $selectedGenre . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM movies WHERE is_active = 1 ORDER BY release_date DESC");
}
$movies = $stmt->fetchAll();

include 'includes/header.php';
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-header-title fade-in">Афиша</h1>
            <p class="page-header-subtitle fade-in-delay">Выберите фильм и погрузитесь в мир кинематографа</p>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <div class="filters">
                <a href="afisha.php" class="filter-btn <?= !$selectedGenre ? 'active' : '' ?>">
                    Все фильмы
                </a>
                <?php
                $uniqueGenres = [];
                foreach ($genres as $genreString) {
                    $genreParts = explode(',', $genreString);
                    foreach ($genreParts as $genre) {
                        $genre = trim($genre);
                        if (!in_array($genre, $uniqueGenres)) {
                            $uniqueGenres[] = $genre;
                        }
                    }
                }
                sort($uniqueGenres);
                
                foreach ($uniqueGenres as $genre):
                ?>
                <a href="afisha.php?genre=<?= urlencode($genre) ?>" 
                   class="filter-btn <?= $selectedGenre === $genre ? 'active' : '' ?>">
                    <?= h($genre) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Movies Grid Section -->
    <section class="afisha-page">
        <div class="container">
            <?php if (empty($movies)): ?>
                <div class="no-movies">
                    <p>К сожалению, фильмы в этой категории не найдены.</p>
                    <a href="afisha.php" class="hero-btn" style="margin-top: 2rem;">
                        <span>Вернуться к афише</span>
                        <div class="btn-glow"></div>
                    </a>
                </div>
            <?php else: ?>
                <div class="movies-grid">
                    <?php foreach ($movies as $movie): ?>
                    <div class="movie-card">
                        <div class="movie-poster">
                            <img src="<?= h($movie['poster']) ?>" alt="<?= h($movie['title']) ?>">
                            <div class="movie-overlay">
                                <a href="movie.php?id=<?= $movie['id'] ?>" class="movie-btn">Купить билет</a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <h3 class="movie-title"><?= h($movie['title']) ?></h3>
                            <div class="movie-meta">
                                <span class="genre"><?= h($movie['genre']) ?></span>
                                <span class="duration"><?= formatDuration($movie['duration']) ?></span>
                            </div>
                            <div class="movie-age-rating"><?= h($movie['age_rating']) ?></div>
                            <div class="movie-date">Премьера: <?= formatDate($movie['release_date']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
