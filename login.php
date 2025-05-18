<?php
require_once 'mysqli_databaseconnect.php';

// Начинаем сессию PHP для возможности установки cookie
// Важно: session_start() должен быть вызван до любого вывода в браузер
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';

// Проверяем, была ли отправлена форма авторизации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Базовая валидация
    if (empty($username) || empty($password)) {
        $message = "Пожалуйста, введите имя пользователя и пароль.";
    } else {
        // Проверяем соединение
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Ищем пользователя в базе данных по имени
        $sql = "SELECT id, username, password FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Пользователь найден
            $user = $result->fetch_assoc();
            $hashed_password = $user['password'];

            // Проверяем введенный пароль с хешем из базы данных
            if (password_verify($password, $hashed_password)) {
                // Пароль совпадает - успешная авторизация

                // Генерируем уникальный токен сессии
                $session_token = bin2hex(random_bytes(32)); // Генерируем случайный токен

                // Устанавливаем время истечения сессии (например, 1 час)
                $expires_at = date('Y-m-d H:i:s', time() + 3600); // Сессия активна 1 час

                // Сохраняем сессию в таблице sessions
                $insert_session_sql = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)";
                $stmt_session = $conn->prepare($insert_session_sql);
                $stmt_session->bind_param("iss", $user['id'], $session_token, $expires_at);

                if ($stmt_session->execute() === TRUE) {
                    // Сессия успешно сохранена в БД, устанавливаем cookie
                    // Устанавливаем cookie с токеном сессии. Срок жизни cookie соответствует expires_at.
                    setcookie("session_token", $session_token, time() + 3600, "/"); // Устанавливаем cookie на 1 час

                    // Перенаправляем пользователя на главную страницу (или другую страницу)
                    header("Location: index.html"); // Можете изменить на другую страницу
                    exit(); // Важно завершить выполнение скрипта после перенаправления

                } else {
                    $message = "Ошибка при создании сессии: " . $stmt_session->error;
                }
                $stmt_session->close();

            } else {
                // Пароль не совпадает
                $message = "Неверное имя пользователя или пароль.";
            }
        } else {
            // Пользователь не найден
            $message = "Неверное имя пользователя или пароль.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация - Столплит</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Дополнительные стили для формы авторизации */
        .login-form {
            width: 300px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .login-form input[type="submit"] {
            background-color: #ff8000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }
        .login-form input[type="submit"]:hover {
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
            flex: 1 0 auto;
        }
        .footer-cell {
            flex-shrink: 0;
        }
         .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            background-color: #f2dede; /* Стиль ошибки */
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
            <!-- Здесь можно добавить информацию о входе, если нужно -->
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
        <!-- Левая колонка -->
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
            <h1>Авторизация</h1>

            <?php if (!empty($message)): ?>
                <div class="message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="login-form">
                <form action="login.php" method="post">
                    <input type="text" name="username" placeholder="Имя пользователя (логин)" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <input type="submit" value="Войти">
                    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p> <!-- Ссылка на register.php -->
                </form>
            </div>
        </td>

        <!-- Правая колонка -->
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