<?php
require_once 'config.php';

$pageTitle = 'Главная';

// Получаем активные фильмы для раздела "Сейчас в прокате" (ограничим 6 фильмами)
$stmt = $pdo->prepare("SELECT * FROM movies WHERE is_active = 1 ORDER BY release_date DESC LIMIT 6");
$stmt->execute();
$movies = $stmt->fetchAll();

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
            <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070" alt="Cinema" class="hero-image">
        </div>
        <div class="hero-content">
            <h1 class="hero-title fade-in">Искусство большого экрана</h1>
            <p class="hero-subtitle fade-in-delay">Погрузитесь в мир кинематографа с технологиями будущего</p>
            <a href="afisha.php" class="hero-btn fade-in-delay-2">
                <span>Смотреть афишу</span>
                <div class="btn-glow"></div>
            </a>
        </div>
        <div class="scroll-indicator">
            <span></span>
        </div>
    </section>

    <!-- Advantages Section -->
    <section class="advantages" id="advantages">
        <div class="container">
            <h2 class="section-title">Премиальный опыт</h2>
            <div class="advantages-grid">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2" stroke-width="1.5"/>
                            <path d="M16 3h4v4M8 3H4v4M16 21h4v-4M8 21H4v-4" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h3>IMAX Laser</h3>
                    <p>Кристальное изображение с непревзойденной яркостью и контрастностью</p>
                </div>
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                            <path d="M12 6v6l4 2" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>4K HDR</h3>
                    <p>Беспрецедентная детализация в каждом кадре</p>
                </div>
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 18V5l12-2v13" stroke-width="1.5"/>
                            <circle cx="6" cy="18" r="3" stroke-width="1.5"/>
                            <circle cx="18" cy="16" r="3" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h3>Dolby Atmos</h3>
                    <p>Объёмный звук, погружающий в атмосферу фильма</p>
                </div>
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 7h-9M14 17H5M15 12a3 3 0 1 0 6 0 3 3 0 1 0-6 0zM3 12a3 3 0 1 0 6 0 3 3 0 1 0-6 0z" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h3>Премиум залы</h3>
                    <p>Кожаные кресла с подогревом и максимальным комфортом</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Movies Section - Now in Cinema -->
    <section class="afisha" id="afisha">
        <div class="container">
            <h2 class="section-title">Сейчас в прокате</h2>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="<?= h($movie['poster']) ?>" alt="<?= h($movie['title']) ?>">
                        <div class="movie-overlay">
                            <a href="movie.php?id=<?= $movie['id'] ?>" class="movie-btn">Подробнее</a>
                        </div>
                    </div>
                    <div class="movie-info">
                        <h3 class="movie-title"><?= h($movie['title']) ?></h3>
                        <div class="movie-meta">
                            <span class="genre"><?= h($movie['genre']) ?></span>
                            <span class="duration"><?= formatDuration($movie['duration']) ?></span>
                        </div>
                        <div class="movie-date">Премьера: <?= formatDate($movie['release_date']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 4rem;">
                <a href="afisha.php" class="hero-btn">
                    <span>Смотреть полную афишу</span>
                    <div class="btn-glow"></div>
                </a>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews" id="reviews">
        <div class="container">
            <h2 class="section-title">Что говорят наши гости</h2>
            <div class="reviews-grid">
                <div class="review-card">
                    <div class="review-rating">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="review-text">«Невероятное качество изображения и звука. Смотрел "Дюну" в формате IMAX — это незабываемый опыт. Кинотеатр будущего уже здесь.»</p>
                    <div class="review-author">
                        <div class="author-avatar">АС</div>
                        <div class="author-info">
                            <div class="author-name">Алексей Смирнов</div>
                            <div class="author-date">5 февраля 2026</div>
                        </div>
                    </div>
                </div>

                <div class="review-card">
                    <div class="review-rating">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="review-text">«Премиальные кресла с подогревом — это что-то невероятное! Атмосфера, дизайн, сервис — всё на высшем уровне. Теперь только сюда.»</p>
                    <div class="review-author">
                        <div class="author-avatar">МК</div>
                        <div class="author-info">
                            <div class="author-name">Мария Королёва</div>
                            <div class="author-date">2 февраля 2026</div>
                        </div>
                    </div>
                </div>

                <div class="review-card">
                    <div class="review-rating">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="review-text">«Dolby Atmos в этом кинотеатре работает просто волшебно. Каждый звук, каждая нота — всё ощущается физически. Лучший звук в городе.»</p>
                    <div class="review-author">
                        <div class="author-avatar">ДН</div>
                        <div class="author-info">
                            <div class="author-name">Дмитрий Новиков</div>
                            <div class="author-date">28 января 2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
