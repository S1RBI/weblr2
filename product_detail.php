<?php
require_once 'mysqli_databaseconnect.php';

$product = null;

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получаем alias из URL
if (isset($_GET['alias'])) {
    $product_alias = $conn->real_escape_string($_GET['alias']);

    // Запрос к базе данных
    $sql = "SELECT * FROM product WHERE alias = '$product_alias' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Товар найден
        $product = $result->fetch_assoc();
    } else {
        // Товар не найден
        $error_message = "Товар с алиасом '" . htmlspecialchars($product_alias) . "' не найден.";
    }
} else {
    // Alias не предоставлен в URL
    $error_message = "Не указан алиас товара.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Столплит - <?php echo isset($product['meta_title']) ? htmlspecialchars($product['meta_title']) : 'Товар не найден'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="description" content="<?php echo isset($product['meta_description']) ? htmlspecialchars($product['meta_description']) : 'Страница с информацией о товаре'; ?>">
    <meta name="keywords" content="<?php echo isset($product['meta_keywords']) ? htmlspecialchars($product['meta_keywords']) : 'товар, описание'; ?>">
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
        <td><a href="catalog.html" class="active">Каталог</a></td>
        <td><a href="contacts.html">Контакты</a></td>
        <td class="search-cell">
            <input type="text" value="поиск"> <input type="button" value="Искать">
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
        </td>

        <td class="main-content-cell">
            <?php if (isset($product)): ?>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <hr>

                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">

                <h3>Описание товара</h3>
                <p><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>

                <h3>Подробное описание товара</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <?php if ($product['price'] > 0): // Отображаем цену, только если она больше 0 ?>
                   <h3>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</h3>
                <?php endif; ?>

                <?php
                // TODO: Здесь можно добавить вывод свойств товара из product_properties
                // TODO: Здесь можно добавить вывод дополнительных изображений из product_images
                ?>

                <p><a href="edit_product.php?id=<?php echo $product['id']; ?>">Редактировать товар</a></p>

            <?php elseif (isset($error_message)): ?>
                <h2>Ошибка</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            <?php else: ?>
                 <h2>Ошибка</h2>
                <p>Произошла неизвестная ошибка.</p>
            <?php endif; ?>

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