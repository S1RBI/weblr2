<?php
require_once 'auth_check.php'; // Включаем скрипт проверки авторизации
require_once 'mysqli_databaseconnect.php'; // Включаем подключение к базе данных

$searchQuery = '';
$matches = []; // Для хранения результатов поиска из БД
$error_message = '';

// --- Логика поиска (до вывода HTML) ---
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
        // Получаем и экранируем поисковый запрос
        $searchQuery = trim($_GET['search_query']);
        $safeSearchQuery = mysqli_real_escape_string($conn, $searchQuery);

        // SQL запрос для поиска по названию, краткому и полному описанию
        // Используем LEFT JOIN с product_images для получения основного изображения
        $sql = "SELECT
                    p.id,
                    p.name,
                    p.alias,
                    p.short_description,
                    p.description,
                    p.price,
                    (SELECT image FROM product_images WHERE product_id = p.id ORDER BY id ASC LIMIT 1) AS main_image
                FROM
                    product p
                WHERE
                    p.name LIKE '%" . $safeSearchQuery . "%' OR
                    p.short_description LIKE '%" . $safeSearchQuery . "%' OR
                    p.description LIKE '%" . $safeSearchQuery . "%'";

        $result = $conn->query($sql);

        if ($result) { // Проверяем, успешно ли выполнен запрос
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $matches[] = $row;
                }

                 // Логика редиректа при единственном совпадении
                if (count($matches) === 1) {
                    $singleMatch = reset($matches);
                     // Проверяем наличие алиаса для редиректа на страницу деталей товара
                    if (isset($singleMatch['alias']) && !empty($singleMatch['alias'])) {
                        // Важно: редирект ДО любого вывода HTML
                        header("Location: product_detail.php?alias=" . htmlspecialchars($singleMatch['alias']));
                        exit; // Обязательно завершаем скрипт
                    }
                    // Если алиаса нет, редиректа не будет, скрипт продолжится
                }

            } else {
                // Если запрос был, но ничего не найдено
                // $matches останется пустым, что будет обработано ниже в HTML
            }
        } else {
            // Обработка ошибки выполнения запроса
            $error_message = "Ошибка выполнения запроса: " . $conn->error;
        }

    } else if (isset($_GET['search_query'])) { // Запрос был передан, но он пустой
        $error_message = "Пожалуйста, введите поисковый запрос.";
    }
    // Если параметр search_query вообще не передавался, $searchQuery останется пустым
}

// Безопасное значение поискового запроса для вывода в HTML
$safeSearchQuery = isset($searchQuery) ? htmlspecialchars($searchQuery) : '';

// Соединение закрывается в auth_check.php после всех операций,
// или будет закрыто автоматически в конце скрипта, если не использовать персистентное соединение.
// $conn->close();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результаты поиска - Столплит</title>
    <link rel="stylesheet" href="css/style.css">
     <style>
        /* Стили для страницы результатов поиска */
        .search-results-list {
            list-style: none;
            padding: 0;
            margin-top: 15px;
         }
        .search-results-list li {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: flex;
            align-items: center;
        }
        .search-results-list li:last-child {
            border-bottom: none;
         }
        .search-results-list img {
            max-width: 80px; /* Размер миниатюры */
            height: auto;
            margin-right: 15px;
            border: 1px solid #ddd;
            flex-shrink: 0; /* Чтобы картинка не сжималась */
         }
         .search-results-list .details {
            flex-grow: 1; /* Чтобы текст занимал оставшееся место */
         }
        .search-results-list a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 1.1em;
         }
        .search-results-list a:hover {
            color: #cc0000;
         }
        .search-results-list p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 0.9em;
         }
        .no-results {
            color: #777;
            font-style: italic;
            margin-top: 15px;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 15px;
        }
        .search-prompt {
             margin-top: 15px;
        }
     </style>
</head>
<body>

<!-- Шапка сайта -->
<table class="layout-table header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell"><a href="index.php"><img src="img/logo.png" alt="Логотип Столплит"></a></td>
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

<!-- Навигация сайта -->
<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.php">Главная</a></td>
        <td><a href="catalog.php">Каталог</a></td>
        <td><a href="contacts.html">Контакты</a></td>
        <td class="search-cell">
            <!-- Форма поиска на странице результатов -->
            <form action="search.php" method="GET">
                <input type="text" name="search_query" placeholder="поиск товара..." value="<?php echo $safeSearchQuery; ?>">
                <input type="submit" value="Искать">
            </form>
        </td>
    </tr>
</table>

<!-- Основной контент сайта -->
<table class="layout-table content-table" cellpadding="0" cellspacing="0">
    <tr>
        <!-- Левая колонка (сайдбар) -->
        <td class="sidebar-left">
             <a href="index.php">Главная</a><br>
             <a href="catalog.php">Каталог</a><br>
             <nav> <!-- Меню категорий (ссылки ведут в каталог) -->
                 <a href="catalog.php?category=Диваны">Диваны</a><br>
                 <a href="catalog.php?category=Шкафы">Шкафы</a><br>
                 <a href="catalog.php?category=Кровати">Кровати</a><br>
                 <a href="catalog.php?category=Столы">Столы</a><br>
             </nav>
             <a href="contacts.html">Контакты</a>
        </td>

        <!-- Центральная колонка (результаты поиска) -->
        <td class="main-content-cell">
            <?php // НАЧАЛО БЛОКА ВЫВОДА РЕЗУЛЬТАТОВ ?>

            <?php if (!empty($searchQuery)): // Показываем заголовок, если был непустой запрос ?>
                <h2>Результаты поиска по запросу: "<?php echo $safeSearchQuery; ?>"</h2>
            <?php else: // Если запрос пустой или не задан ?>
                <h2>Поиск товаров</h2>
            <?php endif; ?>
            <hr>

            <?php
            // 1. Выводим сообщение об ошибке (например, пустой запрос или ошибка запроса)
            if (!empty($error_message)) {
                echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
            }
            // 2. Если был выполнен поиск (запрос не был пустым) и нет ошибок
            elseif (!empty($searchQuery)) {
                // И найдены результаты
                if (count($matches) > 0) {
                    echo "<p>Найдено товаров: " . count($matches) . "</p>"; // Выводим количество
                    echo "<ul class='search-results-list'>"; // Начинаем список
                    foreach ($matches as $product) {
                        // Безопасно извлекаем данные
                        $product_name = isset($product['name']) ? htmlspecialchars($product['name']) : 'Без названия';
                        $product_alias = isset($product['alias']) ? htmlspecialchars($product['alias']) : '#'; // Используем алиас для ссылки
                        $main_image = isset($product['main_image']) ? htmlspecialchars($product['main_image']) : 'img/placeholder_thumb.jpg'; // Получаем основное изображение или заглушку
                        $short_description = isset($product['short_description']) ? htmlspecialchars($product['short_description']) : ''; // Используем short_description

                        echo "<li>"; // Открываем элемент списка
                        // Выводим изображение
                        echo "<img src='" . $main_image . "' alt='" . $product_name . "'>";

                        echo "<div class='details'>"; // Открываем div для текста
                            // Ссылка ведет на product_detail.php с алиасом
                            echo "<a href='product_detail.php?alias=" . $product_alias . "'>" . $product_name . "</a>";
                            if (!empty($short_description)) {
                                echo "<p>" . $short_description . "</p>";
                            }
                        echo "</div>"; // Закрываем div для текста
                        echo "</li>"; // Закрываем элемент списка
                    }
                    echo "</ul>"; // Закрываем список
                } else {
                    // Если запрос был, но ничего не найдено
                    echo "<p class='no-results'>По вашему запросу ничего не найдено.</p>";
                }
            }
            // 3. Если страница открыта без запроса (и не было ошибки пустого запроса)
            elseif (empty($error_message) && empty($searchQuery)) {
                 echo "<p class='search-prompt'>Пожалуйста, введите поисковый запрос в поле выше.</p>";
            }
            ?>

            <?php // КОНЕЦ БЛОКА ВЫВОДА РЕЗУЛЬТАТОВ ?>
        </td>

        <!-- Правая колонка (сайдбар с баннерами) -->
        <td class="sidebar-right">
             <a href="#"><img src="img/banner1.jpg" alt="Рекламный баннер 1"></a>
             <a href="#"><img src="img/banner2.jpg" alt="Рекламный баннер 2"></a>
             <a href="#"><img src="img/banner3.jpg" alt="Рекламный баннер 3"></a>
        </td>
    </tr>

    <!-- Подвал сайта -->
    <tr>
        <td colspan="3" class="footer-cell">
            © 2025 Столплит. Все права защищены.
        </td>
    </tr>
</table>

</body>
</html>
