<?php
require_once 'auth_check.php'; // Включаем скрипт проверки авторизации
require_once 'mysqli_databaseconnect.php';

$products = [];
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : ''; // Параметр сортировки

// Проверяем соединение
// Соединение теперь устанавливается в auth_check.php, используем глобальную переменную $conn
// if ($conn->connect_error) {
//     die("Ошибка подключения: " . $conn->connect_error);
// }


// Формируем SQL запрос
// Обновлен запрос для получения основного изображения из product_images
$sql = "SELECT p.id, p.name, p.alias, p.short_description, p.price,
               (SELECT image FROM product_images WHERE product_id = p.id ORDER BY id ASC LIMIT 1) AS main_image
        FROM product p";

$where_clauses = [];

if ($filter_category !== 'all') {
    // Пример фильтрации по ключевым словам
    $safe_category = mysqli_real_escape_string($conn, $filter_category);
    // Учитываем регистр для корректной фильтрации, если meta_keywords в нижнем регистре
    $where_clauses[] = "LOWER(p.meta_keywords) LIKE '%" . strtolower($safe_category) . "%'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Добавляем логику сортировки
if (!empty($sort_by)) {
    // Проверяем допустимые столбцы для сортировки
    $allowed_sort_columns = ['name', 'price'];
    if (in_array($sort_by, $allowed_sort_columns)) {
        $sql .= " ORDER BY p." . $sort_by;
        // Можно добавить ASC/DESC, если нужно
         $sql .= " ASC"; // Или DESC
    }
}


$result = $conn->query($sql);

if ($result) { // Проверяем, успешно ли выполнен запрос
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    // Обработка ошибки выполнения запроса
    echo "Ошибка выполнения запроса: " . $conn->error;
}


// Соединение закрывается в auth_check.php после всех операций,
// или будет закрыто автоматически в конце скрипта, если не использовать персистентное соединение.
// $conn->close();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог - Столплит</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-card.hidden {
            display: none; /* This might not be needed anymore with server-side filtering */
        }

        #category-filters a.filter-active {
            font-weight: bold;
            color: #cc0000;
        }
      
        #category-filters {
            margin: 0;
            padding: 0;

        }

        
         #category-filters a {
            display: inline-block; 
            margin-bottom: 5px;
         }
         .sort-form {
             margin-bottom: 20px;
         }
         .sort-form label {
             margin-right: 10px;
         }
    </style>
</head>
<body>

<!-- Таблица Шапки -->
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
            
            <nav id="category-filters">
                <a href="index.php">Главная</a><br>
                <a href="catalog.php?category=all" class="<?php echo ($filter_category === 'all') ? 'filter-active' : ''; ?>"><b>Каталог</b></a><br>
                <a href="catalog.php?category=Диваны" class="<?php echo ($filter_category === 'Диваны') ? 'filter-active' : ''; ?>">Диваны</a><br>
                <a href="catalog.php?category=Шкафы" class="<?php echo ($filter_category === 'Шкафы') ? 'filter-active' : ''; ?>">Шкафы</a><br>
                <a href="catalog.php?category=Кровати" class="<?php echo ($filter_category === 'Кровати') ? 'filter-active' : ''; ?>">Кровати</a><br>
                <a href="catalog.php?category=Столы" class="<?php echo ($filter_category === 'Столы') ? 'filter-active' : ''; ?>">Столы</a><br>
                <a href="contacts.html">Контакты</a>
            <a href="guestbook.html">Отзыв</a>
            </nav>
            
        </td>

        <td class="main-content-cell">
            <h2>Каталог товаров</h2>
            <hr>

            <form action="catalog.php" method="GET" class="sort-form">
                 <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category); ?>"> <!-- Сохраняем текущий фильтр -->
                <label for="sort">Сортировать по:</label>
                <select name="sort" id="sort">
                    <option value="" <?php echo ($sort_by === '') ? 'selected' : ''; ?>>-- Выберите --</option>
                    <option value="name" <?php echo ($sort_by === 'name') ? 'selected' : ''; ?>>Названию</option>
                    <option value="price" <?php echo ($sort_by === 'price') ? 'selected' : ''; ?>>Цене</option>
                </select>
                <button type="submit">Сортировать</button>
            </form>

            <div class="catalog-grid" id="catalog-grid">

                <?php if (!empty($products)):
                    ?>
                    <?php foreach ($products as $product):
                         // Используем 'main_image' из запроса
                         $image_path = !empty($product['main_image']) ? htmlspecialchars($product['main_image']) : 'img/placeholder_thumb.jpg';
                         ?>
                        <div class="product-card" data-category="<?php // Здесь можно добавить категорию, если она будет в таблице ?>">
                            <a href="product_detail.php?alias=<?php echo htmlspecialchars($product['alias']); ?>">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                             <?php if ($product['price'] > 0):
                                  ?>
                                <p>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>
                            <?php endif;
                             ?>
                            <a href="product_detail.php?alias=<?php echo htmlspecialchars($product['alias']); ?>">Подробнее...</a>
                        </div>
                    <?php endforeach;
                 ?>
                <?php else:
                     ?>
                    <p>Нет товаров, соответствующих выбранным критериям.</p>
                <?php endif;
                 ?>

                <!-- / Карточки товаров -->

            </div> <!-- конец catalog-grid -->
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

<!-- JAVASCRIPT ДЛЯ КЛИЕНТСКОЙ ФИЛЬТРАЦИИ БОЛЬШЕ НЕ НУЖЕН, НО ОСТАВЛЕН В КАЧЕСТВЕ ПРИМЕРА -->
<script>
    // Этот JavaScript код теперь не используется для фильтрации, так как она выполняется на сервере.
    // Он может быть удален, если не выполняет других полезных функций.
    document.addEventListener('DOMContentLoaded', function() {
        const filterContainer = document.getElementById('category-filters');
        const productGrid = document.getElementById('catalog-grid');
        const productCards = productGrid.querySelectorAll('.product-card');
        const filterLinks = filterContainer.querySelectorAll('a'); // Изменено для выбора всех ссылок в контейнере

        // Оставлен для подсветки активной ссылки (хотя PHP уже делает это)
        const urlParams = new URLSearchParams(window.location.search);
        const activeCategory = urlParams.get('category') || 'all';

        filterLinks.forEach(link => {
             const linkCategory = new URLSearchParams(link.search).get('category') || 'all';
             if (linkCategory === activeCategory) {
                link.classList.add('filter-active');
            } else {
                 link.classList.remove('filter-active');
            }
        });


    });
</script>

</body>
</html>