<?php
require_once 'mysqli_databaseconnect.php';

$message = '';

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем соединение
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Получаем и экранируем данные из формы
    $manufacturer_id = isset($_POST['manufacturer_id']) ? (int)$_POST['manufacturer_id'] : 1; // Значение по умолчанию
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $alias = mysqli_real_escape_string($conn, $_POST['alias']);
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00; // Значение по умолчанию
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $available = isset($_POST['available']) ? (int)$_POST['available'] : 1; // Значение по умолчанию
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);

    // SQL запрос для вставки данных
    $sql = "INSERT INTO product (manufacturer_id, name, alias, short_description, description, price, image, available, meta_keywords, meta_description, meta_title)
            VALUES ('$manufacturer_id', '$name', '$alias', '$short_description', '$description', '$price', '$image', '$available', '$meta_keywords', '$meta_description', '$meta_title')";

    if ($conn->query($sql) === TRUE) {
        $message = "Новый товар успешно добавлен.";
    } else {
        $message = "Ошибка: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить товар</title>
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
        <td><a href="index.html">Главная</a></td>
        <td><a href="catalog.html">Каталог</a></td>
        <td><a href="contacts.html">Контакты</a></td>
        <td class="search-cell">
            <form action="search.php" method="GET">
                <input type="text" name="search_query" placeholder="поиск товара...">
                <input type="submit" value="Искать">
            </form>
        </td>
    </tr>
</table>

<table class="layout-table content-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="sidebar-left">
             <a href="index.html">Главная</a><br>
            <a href="catalog.html"><b>Каталог</b></a><br>
            <a href="#">Диваны</a><br>
            <a href="#">Шкафы</a><br>
            <a href="#">Кровати</a><br>
            <a href="#">Столы</a><br>
            <a href="contacts.html">Контакты</a>
             <a href="guestbook.html">Отзыв</a><br>
             <a href="add_product.php">Добавить товар</a> <!-- Ссылка на эту страницу -->
        </td>

        <td class="main-content-cell">
            <h2>Добавить новый товар</h2>
            <hr>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo (strpos($message, 'Ошибка') !== false) ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="add_product.php" method="post">

                    <label for="name">Название товара:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="alias">Алиас (для URL):</label>
                    <input type="text" id="alias" name="alias" required>

                     <label for="manufacturer_id">ID производителя:</label>
                    <input type="number" id="manufacturer_id" name="manufacturer_id" value="1">

                    <label for="short_description">Краткое описание:</label>
                    <textarea id="short_description" name="short_description" required></textarea>

                    <label for="description">Полное описание:</label>
                    <textarea id="description" name="description"></textarea>

                    <label for="price">Цена:</label>
                    <input type="number" id="price" name="price" step="0.01" value="0.00">

                    <label for="image">Путь к изображению:</label>
                    <input type="text" id="image" name="image" value="img/placeholder_thumb.jpg">

                    <label for="available">Доступность (1 - да, 0 - нет):</label>
                    <input type="number" id="available" name="available" value="1" min="0" max="1">

                    <label for="meta_keywords">Meta Keywords:</label>
                    <input type="text" id="meta_keywords" name="meta_keywords">

                    <label for="meta_description">Meta Description:</label>
                    <input type="text" id="meta_description" name="meta_description">

                    <label for="meta_title">Meta Title:</label>
                    <input type="text" id="meta_title" name="meta_title">

                    <button type="submit">Добавить товар</button>

                </form>
            </div>

        </td>

        <td class="sidebar-right">
             <a href="#"><img src="img/banner1.jpg" alt="Рекламный баннер 1"></a>
             <a href="#"><img src="img/banner2.jpg" alt="Рекламный баннер 2"></a>
             <a href="#"><img src="img/banner3.jpg" alt="Рекламный баннер 3"></a>
        </td>
    </tr>
    <tr>
        <td colspan="3" class="footer-cell">
            © 2025 Столплит. Все права защищены.
        </td>
    </tr>
</table>

</body>
</html>