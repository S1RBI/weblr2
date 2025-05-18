<?php
require_once 'auth_check.php'; // Включаем скрипт проверки авторизации
require_once 'mysqli_databaseconnect.php';

$product = null;
$images = []; // Для хранения изображений товара
$properties = []; // Для хранения свойств товара
$error_message = '';

// Проверяем соединение
// Соединение теперь устанавливается в auth_check.php, используем глобальную переменную $conn
// if ($conn->connect_error) {
//     die("Ошибка подключения: " . $conn->connect_error);
// }

// Получаем alias из URL
if (isset($_GET['alias'])) {
    $product_alias = $conn->real_escape_string($_GET['alias']);

    // Запрос к таблице product
    $sql_product = "SELECT id, name, alias, short_description, description, price, manufacturer_id, available, meta_keywords, meta_description, meta_title FROM product WHERE alias = '$product_alias' LIMIT 1";
    $result_product = $conn->query($sql_product);

    if ($result_product->num_rows > 0) {
        // Товар найден
        $product = $result_product->fetch_assoc();
        $product_id = $product['id'];

        // Запрос к таблице product_images для получения всех изображений товара
        $sql_images = "SELECT id, image, title FROM product_images WHERE product_id = $product_id ORDER BY id ASC";
        $result_images = $conn->query($sql_images);

        if ($result_images) { // Проверяем успешность выполнения запроса
            if ($result_images->num_rows > 0) {
                while($row_image = $result_images->fetch_assoc()) {
                    $images[] = $row_image;
                }
            }
        } else {
            $error_message .= "Ошибка при получении изображений: " . $conn->error;
        }

        // *********** НОВЫЙ КОД ДЛЯ ПОЛУЧЕНИЯ СВОЙСТВ ТОВАРА ***********
        $sql_properties = "SELECT property_name, property_value, property_price FROM product_properties WHERE product_id = $product_id ORDER BY id ASC";
        $result_properties = $conn->query($sql_properties);

        if ($result_properties) { // Проверяем успешность выполнения запроса
            if ($result_properties->num_rows > 0) {
                while($row_property = $result_properties->fetch_assoc()) {
                    $properties[] = $row_property;
                }
            }
        } else {
            $error_message .= "Ошибка при получении свойств товара: " . $conn->error;
        }
        // **********************************************************

    } else {
        // Товар не найден
        $error_message = "Товар с алиасом '" . htmlspecialchars($product_alias) . "' не найден.";
    }
} else {
    // Alias не предоставлен в URL
    $error_message = "Не указан алиас товара.";
}

// Соединение закрывается в auth_check.php после всех операций,
// или будет закрыто автоматически в конце скрипта, если не использовать персистентное соединение.
// $conn->close();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Столплит - <?php echo isset($product['meta_title']) ? htmlspecialchars($product['meta_title']) : 'Товар не найден'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="description" content="<?php echo isset($product['meta_description']) ? htmlspecialchars($product['meta_description']) : 'Страница с информацией о товаре'; ?>">
    <meta name="keywords" content="<?php echo isset($product['meta_keywords']) ? htmlspecialchars($product['meta_keywords']) : 'товар, описание'; ?>">
    <style>
        .product-images-gallery img {
            width: 100px;
            height: auto;
            margin-right: 10px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        /* Стили для отображения свойств */
        .product-properties ul {
            list-style: none;
            padding: 0;
        }

        .product-properties li {
            margin-bottom: 10px;
        }

        .property-name {
            font-weight: bold;
        }

        .property-price {
            font-style: italic;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<!-- Таблица Шапки -->
<table class="layout-table header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell">
            <a href="index.php"><img src="img/logo.png" alt="Логотип Столплит"></a>
        </td>
        <td class="title-cell"><h1>Столплит Мебель</h1></td>
        <td class="login-cell">
             <?php if (isset($logged_in_user)): ?>
                Привет, <?php echo htmlspecialchars($logged_in_user['username']); ?>!
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Войти</a>&nbsp;&nbsp;<a href="register.php">Зарегистрироваться</a>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- Таблица Навигации -->
<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.php">Главная</a></td>
        <td><a href="catalog.php" class="active">Каталог</a></td>
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
            <a href="index.php">Главная</a><br>
            <a href="catalog.php"><b>Каталог</b></a><br>
            <a href="#">Диваны</a><br>
            <a href="#">Шкафы</a><br>
            <a href="#">Кровати</a><br>
            <a href="#">Столы</a><br>
            <a href="contacts.html">Контакты</a>
             <a href="guestbook.html">Отзыв</a>
        </td>

        <td class="main-content-cell">
            <?php if (isset($product)): ?>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <hr>

                <div class="product-images-gallery">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['image']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                        <?php endforeach; ?>
                    <?php elseif (isset($product['image']) && !empty($product['image'])): ?>
                        <!-- Если изображений в product_images нет, но есть старый путь (уже удален), можно добавить заглушку -->
                         <img src="img/placeholder_thumb.jpg" alt="Нет изображения">
                    <?php else: ?>
                         <img src="img/placeholder_thumb.jpg" alt="Нет изображения">
                    <?php endif; ?>
                </div>

                <h3>Описание товара</h3>
                <p><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>

                <h3>Подробное описание товара</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <?php if ($product['price'] > 0): // Отображаем цену, только если она больше 0 ?>
                   <h3>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</h3>
                <?php endif; ?>

                <?php
                // *********** НОВЫЙ КОД ДЛЯ ОТОБРАЖЕНИЯ СВОЙСТВ ТОВАРА ***********
                if (!empty($properties)):
                ?>
                    <h3>Характеристики</h3>
                    <div class="product-properties">
                        <ul>
                        <?php foreach ($properties as $property): ?>
                            <li>
                                <span class="property-name"><?php echo htmlspecialchars($property['property_name']); ?>:</span>
                                <?php echo htmlspecialchars($property['property_value']); ?>
                                <?php if ($property['property_price'] > 0): ?>
                                    <span class="property-price">(+<?php echo htmlspecialchars($property['property_price']); ?> руб.)</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php
                endif;
                // ***************************************************************
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
             <a href="#"><img src="img/banner2.jpg" alt="Рекламный банner 2"></a>
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