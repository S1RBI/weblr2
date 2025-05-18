<?php
require_once 'mysqli_databaseconnect.php';

$message = '';

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $username = $_POST['username'];
    $password = $_POST['password'];
    // Можно добавить поле email, если нужно
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    // Базовая валидация
    if (empty($username) || empty($password)) {
        $message = "Имя пользователя и пароль не могут быть пустыми.";
    } else {
        // Хешируем пароль
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Проверяем соединение
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Проверяем, существует ли пользователь с таким именем
        $check_user_sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $stmt_check = $conn->prepare($check_user_sql);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Пользователь с таким именем уже существует.";
        } else {
            // Вставляем нового пользователя в базу данных
            $insert_user_sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_user_sql);
            $stmt_insert->bind_param("sss", $username, $hashed_password, $email);

            if ($stmt_insert->execute() === TRUE) {
                $message = "Регистрация прошла успешно! Теперь вы можете <a href="login.html">войти</a>.";
            } else {
                $message = "Ошибка при регистрации: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Столплит</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Дополнительные стили для формы регистрации */
        .registration-form {
            width: 300px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .registration-form input[type="text"],
        .registration-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box; /* Чтобы padding не увеличивал ширину */
        }
        .registration-form input[type="submit"] {
            background-color: #ff8000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            display: block; /* Делаем кнопку блочным элементом */
            margin: 0 auto; /* Центрируем кнопку */
        }
        .registration-form input[type="submit"]:hover {
            background-color: #cc6600;
        }
        /* Стили для прижатия подвала */
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        .content-table {
            flex: 1 0 auto; /* Основной контент занимает все доступное пространство */
        }
        .footer-cell {
            flex-shrink: 0; /* Подвал не сжимается */
        }
         .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            /* Добавьте стили для успеха/ошибки */
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
            <!--  Здесь можно добавить информацию о входе, если нужно -->
        </td>
    </tr>
</table>

<!-- Таблица Навигации -->
<table class="layout-table nav-table" cellpadding="0" cellspacing="0">
    <tr>
        <td><a href="index.html">Главная</a></td>
        <td><a href="catalog.php">Каталог</a></td>
        <td><a href="contacts.html">Контакты</a></td>
        <td class="search-cell">
            <form action="search.php" method="GET">
                <input type="text" name="search_query" placeholder="поиск..."> 
                <input type="submit" value="Искать">
            </form>
        </td>
    </tr>
</table>

<!-- Таблица Основного контента -->
<table class="layout-table content-table" cellpadding="0" cellspacing="0">
    <tr>
        <!-- Левая колонка (может быть пустой или содержать дополнительное меню) -->
        <td class="sidebar-left">
             <a href="index.html">Главная</a><br>
            <a href="catalog.php"><b>Каталог</b></a><br>
             <a href="#">Диваны</a><br>
            <a href="#">Шкафы</a><br>
            <a href="#">Кровати</a><br>
            <a href="#">Столы</a><br>
            <a href="contacts.html">Контакты</a>
             <a href="guestbook.html">Отзыв</a>
        </td>

        <!-- Центральная колонка -->
        <td class="main-content-cell">
            <h1>Регистрация</h1>

             <?php if (!empty($message)): ?>
                <div class="message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="registration-form">
                <form action="register.php" method="post">  <!-- Замените # на обработчик формы -->
                    <input type="text" name="username" placeholder="Имя пользователя (логин)" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                     <!-- Можно добавить поле email -->
                    <input type="email" name="email" placeholder="Email (необязательно)">
                    <input type="submit" value="Зарегистрироваться">
                    <p>Уже есть аккаунт? <a href="login.html">Войти</a></p>
                </form>
            </div>
        </td>

        <!-- Правая колонка (может быть пустой или содержать баннеры) -->
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