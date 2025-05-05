<?php
require_once 'mysqli_databaseconnect.php';

$products = [];
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Формируем SQL запрос
$sql = "SELECT id, name, alias, short_description, price, image FROM product";
$where_clauses = [];

if ($filter_category !== 'all') {
    // Пример фильтрации по ключевым словам, предполагая, что они содержат категорию
    // Это не идеальное решение. Лучше использовать отдельное поле для категории.
    // Вам может потребоваться настроить это условие в зависимости от ваших данных.
    $safe_category = mysqli_real_escape_string($conn, $filter_category);
    $where_clauses[] = "meta_keywords LIKE '%" . $safe_category . "%'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// TODO: Добавить логику сортировки здесь

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
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

    </style>
</head>
<body>

<table class="layout-table header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell"><a href="index.html"><img src="img/logo.png" alt="Логотип Столплит"></a></td>
        <td class="title-cell"><h1>Столплит Мебель</h1></td>
        <td class="login-cell">
            <a href="login.html">Войти</a>&nbsp;&nbsp;<a href="register.html">Зарегистрироваться</a>
        </td>
    </tr>
</table>

<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.html">Главная</a></td>
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
                <a href="index.html">Главная</a><br>
                <a href="catalog.php?category=all" class="<?php echo ($filter_category === 'all') ? 'filter-active' : ''; ?>"><b>Каталог</b></a><br>
                <a href="catalog.php?category=sofa" class="<?php echo ($filter_category === 'sofa') ? 'filter-active' : ''; ?>">Диваны</a><br>
                <a href="catalog.php?category=wardrobe" class="<?php echo ($filter_category === 'wardrobe') ? 'filter-active' : ''; ?>">Шкафы</a><br>
                <a href="catalog.php?category=bed" class="<?php echo ($filter_category === 'bed') ? 'filter-active' : ''; ?>">Кровати</a><br>
                <a href="catalog.php?category=table" class="<?php echo ($filter_category === 'table') ? 'filter-active' : ''; ?>">Столы</a><br>
                <a href="contacts.html">Контакты</a>
            <a href="guestbook.html">Отзыв</a>
            </nav>
            
        </td>

        <td class="main-content-cell">
            <h2>Каталог товаров</h2>
            <hr>

            <div class="catalog-grid" id="catalog-grid">

                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-category="<?php // Здесь можно добавить категорию, если она будет в таблице ?>">
                            <a href="product_detail.php?alias=<?php echo htmlspecialchars($product['alias']); ?>">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <?php if ($product['price'] > 0): ?>
                                <p>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>
                            <?php endif; ?>
                            <a href="product_detail.php?alias=<?php echo htmlspecialchars($product['alias']); ?>">Подробнее...</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Нет товаров, соответствующих выбранным критериям.</p>
                <?php endif; ?>

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
        const filterLinks = filterContainer.querySelectorAll('a[data-filter]');

        // Оставлен для подсветки активной ссылки (хотя PHP уже делает это)
        const urlParams = new URLSearchParams(window.location.search);
        const activeFilter = urlParams.get('category') || 'all';
        filterLinks.forEach(link => {
            if (link.getAttribute('data-filter') === activeFilter) {
                link.classList.add('filter-active');
            } else {
                 link.classList.remove('filter-active');
            }
             // Удаляем жирность из JS, так как PHP ее добавляет
             if(link.querySelector('b')) {
                 link.innerHTML = link.querySelector('b').innerHTML;
             }
        });
         const catalogLink = filterContainer.querySelector('a[data-filter="all"]');
         if (catalogLink && activeFilter === 'all') {
              catalogLink.innerHTML = `<b>${catalogLink.innerHTML}</b>`;
         }


        // Код клиентской фильтрации, который теперь не нужен:
        /*
        filterContainer.addEventListener('click', function(event) {
            const targetLink = event.target.closest('a[data-filter]');
            if (!targetLink) {
                return;
            }
            event.preventDefault(); // Важно убрать это, если используем серверную фильтрацию
            const filterValue = targetLink.getAttribute('data-filter');

            filterLinks.forEach(link => link.classList.remove('filter-active'));
            targetLink.classList.add('filter-active');

            productCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                if (filterValue === 'all' || cardCategory === filterValue) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
        */
    });
</script>

</body>
</html>