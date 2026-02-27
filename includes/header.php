<?php
$currentPage = getCurrentPage();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>LUMIÈRE — Искусство большого экрана</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800&family=Cinzel:wght@400;500;600;700;800;900&family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">LUMIÈRE</a>
            <ul class="nav-menu">
                <li><a href="index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Главная</a></li>
                <li><a href="afisha.php" class="<?= $currentPage === 'afisha' ? 'active' : '' ?>">Афиша</a></li>
                <li><a href="index.php#advantages" class="<?= $currentPage === 'advantages' ? 'active' : '' ?>">О нас</a></li>
                <li><a href="index.php#reviews" class="<?= $currentPage === 'reviews' ? 'active' : '' ?>">Отзывы</a></li>
                <li><a href="index.php#contact" class="nav-cta">Контакты</a></li>
            </ul>
            <div class="burger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
