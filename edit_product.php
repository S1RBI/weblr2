<?php
require_once 'auth_check.php'; // Включаем скрипт проверки авторизации
require_once 'mysqli_databaseconnect.php';

$product = null;
$images = []; // Для хранения изображений товара
$message = '';

// Проверяем соединение
// Соединение теперь устанавливается в auth_check.php, используем глобальную переменную $conn
// if ($conn->connect_error) {
//     die("Ошибка подключения: " . $conn->connect_error);
// }


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
    // Удален 'image' из $_POST, так как теперь изображения в отдельной таблице
    $available = isset($_POST['available']) ? (int)$_POST['available'] : 1; // Значение по умолчанию
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $meta_description = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);

    // SQL запрос для обновления данных товара в таблице product
    $sql_update_product = "UPDATE product SET
            manufacturer_id = '$manufacturer_id',
            name = '$name',
            alias = '$alias',
            short_description = '$short_description',
            description = '$description',
            price = '$price',
            available = '$available',
            meta_keywords = '$meta_keywords',
            meta_description = '$meta_description',
            meta_title = '$meta_title'
            WHERE id = $id";

    $product_updated = false;
    if ($conn->query($sql_update_product) === TRUE) {
        $message = "Данные товара с ID " . $id . " успешно обновлены.";
        $product_updated = true;
    } else {
        $message = "Ошибка при обновлении данных товара: " . $conn->error;
    }

    // Обработка существующих изображений
    if (isset($_POST['existing_image_ids']) && isset($_POST['existing_image_paths'])) {
        $existing_image_ids = $_POST['existing_image_ids'];
        $existing_image_paths = $_POST['existing_image_paths'];

        // Проверяем, что количество ID и путей совпадает
        if (count($existing_image_ids) === count($existing_image_paths)) {
            for ($i = 0; $i < count($existing_image_ids); $i++) {
                $image_id = (int)$existing_image_ids[$i];
                $image_path = mysqli_real_escape_string($conn, $existing_image_paths[$i]);

                // Обновляем путь изображения в таблице product_images
                $sql_update_image = "UPDATE product_images SET image = '$image_path' WHERE id = $image_id AND product_id = $id";
                // TODO: Можно добавить обновление поля title для изображения, если оно будет в форме

                if ($conn->query($sql_update_image) !== TRUE) {
                     $message .= "<br>Ошибка при обновлении изображения ID " . $image_id . ": " . $conn->error;
                }\
            }
        } else {
             $message .= "<br>Ошибка: Несоответствие данных изображений.";
        }
    }

    // Обработка добавления новых изображений
    if (isset($_POST['new_image_paths'])) {
        $new_image_paths = $_POST['new_image_paths'];
        foreach ($new_image_paths as $new_image_path) {
            $trimmed_path = trim($new_image_path);
            if (!empty($trimmed_path)) {
                 $safe_new_image_path = mysqli_real_escape_string($conn, $trimmed_path);
                 // TODO: Можно добавить поле title для нового изображения
                $sql_insert_image = "INSERT INTO product_images (product_id, image, title) VALUES ($id, '$safe_new_image_path', '')"; // Пустой title пока

                if ($conn->query($sql_insert_image) !== TRUE) {
                     $message .= "<br>Ошибка при добавлении нового изображения: " . $conn->error;
                }\
            }
        }
    }

     // После обновления/добавления, снова получаем данные для отображения в форме
     // Проверяем, было ли обновление товара ИЛИ добавлены новые изображения
     if ($product_updated || (isset($_POST['new_image_paths']) && count(array_filter($_POST['new_image_paths'])) > 0)) {
         $sql_select = "SELECT * FROM product WHERE id = $id LIMIT 1";
         $result_select = $conn->query($sql_select);
         if ($result_select) { // Проверяем успешность запроса
             if ($result_select->num_rows > 0) {
                 $product = $result_select->fetch_assoc();
             }
         } else {
              $message .= "<br>Ошибка при повторном получении данных товара: " . $conn->error;
         }


         // Снова получаем изображения для отображения
         $sql_images = "SELECT id, image, title FROM product_images WHERE product_id = $id ORDER BY id ASC";
         $result_images = $conn->query($sql_images);
         $images = [];
         if ($result_images) { // Проверяем успешность запроса
             if ($result_images->num_rows > 0) {
                 while($row_image = $result_images->fetch_assoc()) {
                     $images[] = $row_image;
                 }
             }
         } else {
              $message .= "<br>Ошибка при повторном получении изображений: " . $conn->error;
         }

     }


} else {
    // Загрузка страницы для редактирования (GET запрос)
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];

        // Запрос к базе данных для получения данных товара
        $sql_product = "SELECT * FROM product WHERE id = $id LIMIT 1";
        $result_product = $conn->query($sql_product);

        if ($result_product) { // Проверяем успешность выполнения запроса
            if ($result_product->num_rows > 0) {
                // Товар найден
                $product = $result_product->fetch_assoc();

                // Запрос к таблице product_images для получения всех изображений товара
                $sql_images = "SELECT id, image, title FROM product_images WHERE product_id = $id ORDER BY id ASC";
                $result_images = $conn->query($sql_images);

                if ($result_images) { // Проверяем успешность выполнения запроса
                    if ($result_images->num_rows > 0) {
                        while($row_image = $result_images->fetch_assoc()) {
                            $images[] = $row_image;
                        }
                    }
                } else {
                     $message = "Ошибка при получении изображений: " . $conn->error;
                }


            } else {
                // Товар не найден
                $message = "Ошибка: Товар с ID " . $id . " не найден.";
            }
        } else {
             $message = "Ошибка при выполнении запроса товара: " . $conn->error;
        }


    } else {
        // ID не предоставлен в URL
        $message = "Ошибка: Не указан ID товара для редактирования.";
    }
}

// Соединение закрывается в auth_check.php после всех операций,
// или будет закрыто автоматически в конце скрипта, если не использовать персистентное соединение.
// $conn->close();

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
            width: calc(100% - 22px);\
            padding: 10px;\
            margin-bottom: 15px;\
            border: 1px solid #ddd;\
            border-radius: 4px;\
        }\
        .form-container textarea {\
            height: 100px;\
            resize: vertical;\
        }\
        .form-container button {\
            background-color: #f0ad4e;\
            color: white;\
            padding: 10px 15px;\
            border: none;\
            border-radius: 4px;\
            cursor: pointer;\
            font-size: 16px;\
        }\
        .form-container button:hover {\
            background-color: #ec971f;\
        }\
         .message {\
            margin-top: 10px;\
            padding: 10px;\
            border-radius: 4px;\
        }\
        .success {\
            background-color: #dff0d8;\
            color: #3c763d;\
            border: 1px solid #d6e9c6;\
        }\
        .error {\
            background-color: #f2dede;\
            color: #a94442;\
            border: 1px solid #ebccd1;\
        }\
         .image-preview {\
            max-width: 100px;\
            height: auto;\
            margin-top: 5px;\
            border: 1px solid #ddd;\
            padding: 3px;\
        }\
         .image-item {\
             margin-bottom: 15px;\
             border-bottom: 1px dashed #eee;\
             padding-bottom: 10px;\
         }\
         .image-item:last-child {\
             border-bottom: none;\
             padding-bottom: 0;\
         }\
\
\
    </style>\
</head>\
<body>\
\
<!-- Таблица Шапки -->\
<table class=\"layout-table header-table\" cellpadding=\"0\" cellspacing=\"0\">\
    <tr>\
        <td class=\"logo-cell\">\
            <a href=\"index.php\"><img src=\"img/logo.png\" alt=\"Логотип Столплит\"></a>\
        </td>\
        <td class=\"title-cell\"><h1>Столплит Мебель</h1></td>\
        <td class=\"login-cell\">\
             <?php if (isset($logged_in_user)): ?>\
                Привет, <?php echo htmlspecialchars($logged_in_user[\'username\']); ?>!\
                <a href=\"logout.php\">Выйти</a>\
            <?php else: ?>\
                <a href=\"login.php\">Войти</a>&nbsp;&nbsp;<a href=\"register.php\">Зарегистрироваться</a>\
            <?php endif; ?>\
        </td>\
    </tr>\
</table>\
\
<!-- Таблица Навигации -->\
<table class=\"layout-table nav-table\" cellpadding=\"0\" cellspacing=\"0\">\
    <tr>\
        <td><a href=\"index.php\">Главная</a></td>\
        <td><a href=\"catalog.php\">Каталог</a></td>\
        <td><a href=\"contacts.html\">Контакты</a></td>\
        <td class=\"search-cell\">\
            <form action=\"search.php\" method=\"GET\">\
                <input type=\"text\" name=\"search_query\" placeholder=\"поиск товара...\">\
                <input type=\"submit\" value=\"Искать\">\
            </form>\
        </td>\
    </tr>\
</table>\
\
<table class=\"layout-table content-table\" cellpadding=\"0\" cellspacing=\"0\">\
    <tr>\
        <td class=\"sidebar-left\">\
             <a href=\"index.php\">Главная</a><br>\
            <a href=\"catalog.php\"><b>Каталог</b></a><br>\
             <a href=\"#\">Диваны</a><br>\
            <a href=\"#\">Шкафы</a><br>\
            <a href=\"#\">Кровати</a><br>\
            <a href=\"#\">Столы</a><br>\
            <a href=\"contacts.html\">Контакты</a>\
             <a href=\"guestbook.html\">Отзыв</a><br>\
             <a href=\"add_product.php\">Добавить товар</a>\
        </td>\
\
        <td class=\"main-content-cell\">\
            <h2>Редактировать товар</h2>\
            <hr>\
\
            <?php if (!empty($message)): ?>\
                <div class=\"message <?php echo (strpos($message, \'Ошибка\') !== false) ? \'error\' : \'success\'; ?>\">\
                    <?php echo $message; ?>\
                </div>\
            <?php endif; ?>\
\
            <?php if (isset($product)): ?>\
                <div class=\"form-container\">\
                    <form action=\"edit_product.php\" method=\"post\">\
                        <input type=\"hidden\" name=\"id\" value=\"<?php echo $product[\'id\']; ?>\">\
\
                        <label for=\"name\">Название товара:</label>\
                        <input type=\"text\" id=\"name\" name=\"name\" value=\"<?php echo htmlspecialchars($product[\'name\']); ?>\" required>\
\
                        <label for=\"alias\">Алиас (для URL):</label>\
                        <input type=\"text\" id=\"alias\" name=\"alias\" value=\"<?php echo htmlspecialchars($product[\'alias\']); ?>\" required>\
\
                        <label for=\"manufacturer_id\">ID производителя:</label>\
                        <input type=\"number\" id=\"manufacturer_id\" name=\"manufacturer_id\" value=\"<?php echo htmlspecialchars($product[\'manufacturer_id\']); ?>\">\
\
                        <label for=\"short_description\">Краткое описание:</label>\
                        <textarea id=\"short_description\" name=\"short_description\" required><?php echo htmlspecialchars($product[\'short_description\']); ?></textarea>\
\
                        <label for=\"description\">Полное описание:</label>\
                        <textarea id=\"description\" name=\"description\"><?php echo htmlspecialchars($product[\'description\']); ?></textarea>\
\
                        <label for=\"price\">Цена:</label>\
                        <input type=\"number\" id=\"price\" name=\"price\" step=\"0.01\" value=\"<?php echo htmlspecialchars($product[\'price\']); ?>\">\
\
                         <!-- Удалено поле image, так как теперь изображения в отдельной таблице -->\
                        <?php /*\
                         <label for=\"image\">Путь к основному изображению:</label>\
                        <input type=\"text\" id=\"image\" name=\"image\" value=\"<?php echo htmlspecialchars($product[\'image\']); ?>\">\
                        */ ?>\
\
                        <label for=\"available\">Доступность (1 - да, 0 - нет):</label>\
                        <input type=\"number\" id=\"available\" name=\"available\" value=\"<?php echo htmlspecialchars($product[\'available\']); ?>\" min=\"0\" max=\"1\">\
\
                        <label for=\"meta_keywords\">Meta Keywords:</label>\
                        <input type=\"text\" id=\"meta_keywords\" name=\"meta_keywords\" value=\"<?php echo htmlspecialchars($product[\'meta_keywords\']); ?>\">\
\
                        <label for=\"meta_description\">Meta Description:</label>\
                        <input type=\"text\" id=\"meta_description\" name=\"meta_description\" value=\"<?php echo htmlspecialchars($product[\'meta_description\']); ?>\">\
\
                        <label for=\"meta_title\">Meta Title:</label>\
                        <input type=\"text\" id=\"meta_title\" name=\"meta_title\" value=\"<?php echo htmlspecialchars($product[\'meta_title\']); ?>\">\
\
                        <h3>Изображения</h3>\
\
                        <?php if (!empty($images)): ?>\
                            <?php foreach ($images as $image): ?>\
                                <div class=\"image-item\">\
                                     <input type=\"hidden\" name=\"existing_image_ids[]\" value=\"<?php echo $image[\'id\']; ?>\">\
                                    <label for=\"image_<?php echo $image[\'id\']; ?>\">Путь к изображению ID <?php echo $image[\'id\']; ?>:</label>\
                                    <input type=\"text\" id=\"image_<?php echo $image[\'id\']; ?>\" name=\"existing_image_paths[]\" value=\"<?php echo htmlspecialchars($image[\'image\']); ?>\">\
                                     <img src=\"<?php echo htmlspecialchars($image[\'image\']); ?>\" alt=\"<?php echo htmlspecialchars($image[\'title\']); ?>\" class=\"image-preview\">\
                                     <?php // TODO: Добавить кнопку/ссылку для удаления этого изображения ?>\
                                </div>\
                            <?php endforeach; ?>\
                        <?php else: ?>\
                            <p>Нет загруженных изображений для этого товара.</p>\
                        <?php endif; ?>\
\
                        <h4>Добавить новые изображения:</h4>\
                         <!-- Добавьте несколько полей для новых изображений. Можно добавить кнопку \"Добавить еще поле\" с JS -->\
                         <label for=\"new_image_1\">Путь к новому изображению 1:</label>\
                         <input type=\"text\" id=\"new_image_1\" name=\"new_image_paths[]\">\
\
                         <label for=\"new_image_2\">Путь к новому изображению 2:</label>\
                         <input type=\"text\" id=\"new_image_2\" name=\"new_image_paths[]\">\
\
                         <label for=\"new_image_3\">Путь к новому изображению 3:</label>\
                         <input type=\"text\" id=\"new_image_3\" name=\"new_image_paths[]\">\
                         <?php // TODO: Добавить кнопку \"Добавить еще поле\" с JS для динамического добавления полей ?>\
\
\
\
                        <button type=\"submit\">Сохранить изменения</button>\
\
                    </form>\
                </div>\
            <?php endif; ?>\
\
        </td>\
\
        <td class=\"sidebar-right\">\
             <a href=\"#\"><img src=\"img/banner1.jpg\" alt=\"Рекламный баннер 1\"></a>\
             <a href=\"#\"><img src=\"img/banner2.jpg\" alt=\"Рекламный баннер 2\"></a>\
             <a href=\"#\"><img src=\"img/banner3.jpg\" alt=\"Рекламный баннер 3\"></a>\
        </td>\
    </tr>\
    <tr>\
        <td colspan=\"3\" class=\"footer-cell\">\
            © 2025 Столплит. Все права защищены.\
        </td>\
    </tr>\
</table>\
\
</body>\
</html>", path = "edit_product.php"))
