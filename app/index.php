<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role_id'] == 1): ?>
                    <a href="admin.php">Админ-панель</a>
                <?php else: ?>
                    <a href="cabinet.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход</a> | <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>

<!-- Слайдер -->
 <div class="slider-container">
     <div class="slider">
         <!-- Слайд 1 -->
         <div class="slide active" style="background: linear-gradient(135deg, #1e222d, #4a90e2);">
             <img src="img/slide1.jpg" alt="Сцена" onerror="this.style.display='none'">
             <span>🎭 Главная сцена</span>
         </div>
         <!-- Слайд 2 -->
         <div class="slide" style="background: linear-gradient(135deg, #12151e, #8e44ad);">
             <img src="img/slide2.jpg" alt="Зал" onerror="this.style.display='none'">
             <span>🎻 Симфонический оркестр</span>
         </div>
         <!-- Слайд 3 -->
         <div class="slide" style="background: linear-gradient(135deg, #1e222d, #c0392b);">
             <img src="img/slide3.jpg" alt="Занавес" onerror="this.style.display='none'">
             <span>🩰 Балет «Лебединое озеро»</span>
         </div>
         <!-- Слайд 4 -->
         <div class="slide" style="background: linear-gradient(135deg, #12151e, #27ae60);">
             <img src="img/slide4.jpg" alt="Маски" onerror="this.style.display='none'">
             <span>🎟️ Бронирование билетов</span>
         </div>
     </div>
     <button class="slider-btn prev" onclick="changeSlide(-1)">&#10094;</button>
     <button class="slider-btn next" onclick="changeSlide(1)">&#10095;</button>
 </div>

    <div class="container" style="text-align: center; margin-top: 50px;">
        <h1>Добро пожаловать в театр!</h1>
        <p>Здесь вы можете забронировать билеты на лучшие спектакли.</p>
        <div style="margin-top: 20px;">
            <a href="catalog.php" class="btn" style="display: inline-block; text-decoration: none; padding: 12px 25px; font-size: 1.1rem;">Перейти в афишу 🎟️</a>
        </div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    let slideInterval;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        if (index >= totalSlides) currentSlide = 0;
        if (index < 0) currentSlide = totalSlides - 1;
        slides[currentSlide].classList.add('active');
    }

    function changeSlide(step) {
        currentSlide += step;
        showSlide(currentSlide);
        resetTimer(); // Сбрасываем таймер при ручном переключении
    }

    function nextSlide() {
        currentSlide++;
        showSlide(currentSlide);
    }

    function startTimer() {
        slideInterval = setInterval(nextSlide, 3000); // 3000 мс = 3 секунды
    }

    function resetTimer() {
        clearInterval(slideInterval);
        startTimer();
    }

    // Запуск
    startTimer();
</script>
</body>
</html>
