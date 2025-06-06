<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Столплит - Мебель для вашего дома</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Таблица Шапки -->
<table class="layout-table header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell">
            <a href="index.html"><img src="img/logo.png" alt="Логотип Столплит"></a>
        </td>
        <td class="title-cell"><h1>Столплит Мебель</h1></td>        
        <td class="login-cell">
            <a href="login.html">Войти</a>&nbsp;&nbsp;<a href="register.html">Зарегистрироваться</a>
        </td>
    </tr>
</table>

<!-- Таблица Навигации -->
<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.html" class="active">Главная</a></td>
        <td><a href="catalog.php">Каталог</a></td>
        <td><a href="contacts.html">Контакты</a></td>
        <!-- Можно добавить еще пункты, если нужно -->
        <td class="search-cell">
            <form action="search.php" method="GET">
                <input type="text" name="search_query" placeholder="поиск товара...">
                <input type="submit" value="Искать">
            </form>
        </td>
    </tr>
</table>

<!-- Таблица Основного контента -->
<table class="layout-table content-table" cellpadding="0" cellspacing="0">
    <tr>
        <!-- Левая колонка -->
        <td class="sidebar-left">
            <a href="index.html">Главная</a><br> <!-- Повторяем меню или делаем подразделы -->
            <a href="#about">О нас</a><br>
            <a href="#history">История</a><br>
            <a href="#team">Сотрудники</a><br>
            <a href="catalog.php">Каталог</a><br>
            <a href="contacts.html">Контакты</a>
        </td>

        <!-- Центральная колонка -->
        <td class="main-content-cell">
            <h1>Добро пожаловать в "Столплит"!</h1>
            <p>Ваш надежный партнер в мире качественной и доступной мебели. Мы предлагаем широкий ассортимент для гостиной, спальни, кухни и детской комнаты.</p>

            <hr>

            <h2 id="about">О нас</h2>
            <p>Компания "Столплит" уже много лет радует покупателей современной и функциональной мебелью. Мы используем только качественные материалы и следим за последними тенденциями в дизайне интерьеров.</p>
            <p>Наша миссия - сделать ваш дом уютным и комфортным без лишних затрат.</p>

            <h2 id="history">История фирмы</h2>
            <p>Начиная с небольшого производства, "Столплит" вырос в крупную сеть мебельных магазинов по всей стране. Мы гордимся нашей историей и стремимся к постоянному развитию.</p>

            <h2 id="team">Сотрудники</h2>
            <p>Наша команда - это профессионалы своего дела, готовые помочь вам с выбором мебели и ответить на все ваши вопросы. Мы ценим каждого клиента!</p>

            <hr>

        <!-- Правая колонка -->
        <td class="sidebar-right">
             <a href="#"><img src="img/banner1.jpg" alt="Рекламный баннер 1"></a>
             <a href="#"><img src="img/banner2.jpg" alt="Рекламный баннер 2"></a>
             <a href="#"><img src="img/banner3.jpg" alt="Рекламный баннер 3"></a>
             
        </td>
    </tr>
    <!-- Строка подвала -->
    <tr>
        <td colspan="3" class="footer-cell">
            &copy; 2025 Столплит. Все права защищены.
        </td>
    </tr>
</table>

</body>
</html>