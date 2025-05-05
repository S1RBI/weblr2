<?php
require_once 'mysqli_databaseconnect.php';

$product = null;
$message = '';

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка отправки формы (редактирование)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем и экранируем данные из формы
    $id = (int)$_POST['id']; // Получаем ID из скрытого поля формы
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

    // SQL запрос для обновления данных
    $sql = "UPDATE product SET 
            manufacturer_id = '$manufacturer_id',
            name = '$name',
            alias = '$alias',
            short_description = '$short_description',
            description = '$description',
            price = '$price',
            image = '$image',
            available = '$available',
            meta_keywords = '$meta_keywords',
            meta_description = '$meta_description',
            meta_title = '$meta_title'
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $message = "Данные товара с ID " . $id . " успешно обновлены.";
        // После обновления данных, снова получаем их для отображения в форме
        $sql_select = "SELECT * FROM product WHERE id = $id LIMIT 1";
        $result_select = $conn->query($sql_select);
        if ($result_select->num_rows > 0) {
            $product = $result_select->fetch_assoc();
        }

    } else {
        $message = "Ошибка при обновлении данных: " . $conn->error;
         // В случае ошибки, пытаемся получить старые данные для отображения
         if (isset($id)) {
             $sql_select = "SELECT * FROM product WHERE id = $id LIMIT 1";
             $result_select = $conn->query($sql_select);
             if ($result_select->num_rows > 0) {
                 $product = $result_select->fetch_assoc();
             }
         }
    }

} else {
    // Загрузка страницы для редактирования (GET запрос)
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        // Запрос к базе данных для получения данных товара
        $sql = "SELECT * FROM product WHERE id = $id LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Товар найден
            $product = $result->fetch_assoc();
        } else {
            // Товар не найден
            $message = "Ошибка: Товар с ID " . $id . " не найден.";
        }
    } else {
        // ID не предоставлен в URL
        $message = "Ошибка: Не указан ID товара для редактирования.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать товар</title>
    <link rel="stylesheet" href="css/style.css">
     <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container textarea,
        .form-container input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container textarea {
            height: 100px;
            resize: vertical;
        }
        .form-container button {
            background-color: #f0ad4e;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #ec971f;
        }
         .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
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
             <a href="add_product.php">Добавить товар</a>
        </td>

        <td class="main-content-cell">
            <h2>Редактировать товар</h2>
            <hr>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo (strpos($message, 'Ошибка') !== false) ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($product)): ?>
                <div class="form-container">
                    <form action="edit_product.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                        <label for="name">Название товара:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                        <label for="alias">Алиас (для URL):</label>
                        <input type="text" id="alias" name="alias" value="<?php echo htmlspecialchars($product['alias']); ?>" required>

                        <label for="manufacturer_id">ID производителя:</label>
                        <input type="number" id="manufacturer_id" name="manufacturer_id" value="<?php echo htmlspecialchars($product['manufacturer_id']); ?>">

                        <label for="short_description">Краткое описание:</label>
                        <textarea id="short_description" name="short_description" required><?php echo htmlspecialchars($product['short_description']); ?></textarea>

                        <label for="description">Полное описание:</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>

                        <label for="price">Цена:</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>">

                        <label for="image">Путь к изображению:</label>
                        <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($product['image']); ?>">

                        <label for="available">Доступность (1 - да, 0 - нет):</label>
                        <input type="number" id="available" name="available" value="<?php echo htmlspecialchars($product['available']); ?>" min="0" max="1">

                        <label for="meta_keywords">Meta Keywords:</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" value="<?php echo htmlspecialchars($product['meta_keywords']); ?>">

                        <label for="meta_description">Meta Description:</label>
                        <input type="text" id="meta_description" name="meta_description" value="<?php echo htmlspecialchars($product['meta_description']); ?>">

                        <label for="meta_title">Meta Title:</label>
                        <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($product['meta_title']); ?>">

                        <button type="submit">Сохранить изменения</button>

                    </form>
                </div>
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