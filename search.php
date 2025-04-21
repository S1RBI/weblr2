<?php

require_once 'products_data.php'; 

$searchQuery = '';
$matches = []; 
$error_message = ''; 

// --- Логика поиска (до вывода HTML) ---
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
        $searchQuery = trim($_GET['search_query']);
        $lowerSearchQuery = mb_strtolower($searchQuery, 'UTF-8'); // Используем mbstring

        // Цикл поиска совпадений
        foreach ($products as $productId => $product) {
            if (isset($product['name'])) {
                $lowerProductName = mb_strtolower($product['name'], 'UTF-8');                               if (mb_strpos($lowerProductName, $lowerSearchQuery) !== false) { 
                    $matches[$productId] = $product;
                }
            }
        }
        // Конец цикла поиска

        // Логика редиректа при единственном совпадении
        if (count($matches) === 1) {
            $singleMatch = reset($matches);
            // Проверяем наличие и валидность URL для редиректа
            if (isset($singleMatch['page_url']) && !empty($singleMatch['page_url']) && $singleMatch['page_url'] !== '#') {
                // Важно: редирект ДО любого вывода HTML
                header("Location: " . $singleMatch['page_url']);
                exit; // Обязательно завершаем скрипт
            }
            // Если URL не подходит, редиректа не будет, скрипт продолжится
        }
        // Если редиректа не было (найдено 0 или >1 товаров, или URL невалидный), скрипт продолжается...

    } else if (isset($_GET['search_query'])) { // Запрос был передан, но он пустой
        $error_message = "Пожалуйста, введите поисковый запрос.";
    }
    // Если параметр search_query вообще не передавался, $searchQuery останется пустым
}


// Безопасное значение поискового запроса для вывода в HTML
$safeSearchQuery = isset($searchQuery) ? htmlspecialchars($searchQuery) : '';

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
        <td class="logo-cell"><a href="index.html"><img src="img/logo.png" alt="Логотип Столплит"></a></td>
        <td class="title-cell"><h1>Столплит Мебель</h1></td>
        <td class="login-cell">
            <a href="login.html">Войти</a>  <a href="register.html">Зарегистрироваться</a>
        </td>
    </tr>
</table>

<!-- Навигация сайта -->
<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.html">Главная</a></td>
        <td><a href="catalog.html">Каталог</a></td>
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
             <a href="index.html">Главная</a><br>
             <a href="catalog.html">Каталог</a><br>
             <nav> <!-- Меню категорий (ссылки ведут в каталог) -->
                 <a href="catalog.html#sofa">Диваны</a><br>
                 <a href="catalog.html#wardrobe">Шкафы</a><br>
                 <a href="catalog.html#bed">Кровати</a><br>
                 <a href="catalog.html#table">Столы</a><br>
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
            // 1. Выводим сообщение об ошибке (например, пустой запрос)
            if (!empty($error_message)) {
                echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
            }
            // 2. Если был выполнен поиск (запрос не был пустым)
            elseif (!empty($searchQuery)) {
                // И найдены результаты (больше 0, т.к. 1 результат = редирект, но мог быть невалидный URL)
                if (count($matches) > 0) {
                    echo "<p>Найдено товаров: " . count($matches) . "</p>"; // Выводим количество
                    echo "<ul class='search-results-list'>"; // Начинаем список
                    foreach ($matches as $product) {
                        // Безопасно извлекаем данные
                        $product_name = isset($product['name']) ? htmlspecialchars($product['name']) : 'Без названия';
                        $product_url = isset($product['page_url']) ? htmlspecialchars($product['page_url']) : '#';
                        $thumb_img = isset($product['thumb_img']) ? htmlspecialchars($product['thumb_img']) : '';
                        $product_desc = isset($product['description']) ? htmlspecialchars($product['description']) : '';

                        echo "<li>"; // Открываем элемент списка
                        if (!empty($thumb_img)) {
                            echo "<img src='" . $thumb_img . "' alt='" . $product_name . "'>";
                        }
                        echo "<div class='details'>"; // Открываем div для текста
                            echo "<a href='" . $product_url . "'>" . $product_name . "</a>";
                            if (!empty($product_desc)) {
                                echo "<p>" . $product_desc . "</p>";
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
            elseif (empty($error_message)) {
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