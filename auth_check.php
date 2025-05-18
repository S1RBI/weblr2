<?php
// Начинаем PHP сессию, если еще не начата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_databaseconnect.php';

$logged_in_user = null;

// Проверяем наличие cookie с токеном сессии
if (isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];

    // Проверяем соединение с БД
    if ($conn->connect_error) {
         // В случае ошибки подключения к БД, просто не авторизуем пользователя
         // В реальном приложении нужно более robust error handling
    } else {

        // Ищем сессию в базе данных и проверяем срок действия
        $sql_session = "SELECT user_id, expires_at FROM sessions WHERE session_token = ? AND expires_at > NOW() LIMIT 1";
        $stmt_session = $conn->prepare($sql_session);
        $stmt_session->bind_param("s", $session_token);
        $stmt_session->execute();
        $result_session = $stmt_session->get_result();

        if ($result_session->num_rows > 0) {
            // Сессия найдена и действительна
            $session_data = $result_session->fetch_assoc();
            $user_id = $session_data['user_id'];

            // Получаем данные пользователя
            $sql_user = "SELECT id, username FROM users WHERE id = ? LIMIT 1";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows > 0) {
                $logged_in_user = $result_user->fetch_assoc();

                // Продлеваем сессию в базе данных (например, еще на 1 час)
                $new_expires_at = date('Y-m-d H:i:s', time() + 3600); // Продлить на 1 час
                $sql_update_session = "UPDATE sessions SET expires_at = ? WHERE session_token = ?";
                $stmt_update_session = $conn->prepare($sql_update_session);
                $stmt_update_session->bind_param("ss", $new_expires_at, $session_token);
                $stmt_update_session->execute();
                $stmt_update_session->close();

                 // Обновляем срок действия cookie в браузере
                 setcookie("session_token", $session_token, time() + 3600, "/");

            } else {
                // Пользователь не найден (хотя сессия есть), что-то пошло не так. Удаляем сессию.
                $sql_delete_session = "DELETE FROM sessions WHERE session_token = ?";
                $stmt_delete_session = $conn->prepare($sql_delete_session);
                $stmt_delete_session->bind_param("s", $session_token);
                $stmt_delete_session->execute();
                $stmt_delete_session->close();
                setcookie("session_token", "", time() - 3600, "/"); // Удаляем cookie
            }
            $stmt_user->close();

        } else {
            // Сессия не найдена или истекла. Удаляем cookie, если оно есть.
             if (isset($_COOKIE['session_token'])) {
                 $sql_delete_session = "DELETE FROM sessions WHERE session_token = ?";
                 $stmt_delete_session = $conn->prepare($sql_delete_session);
                 $stmt_delete_session->bind_param("s", $session_token);
                 $stmt_delete_session->execute();
                 $stmt_delete_session->close();
                 setcookie("session_token", "", time() - 3600, "/"); // Удаляем cookie
             }
        }
        $stmt_session->close();
        $conn->close();
    }
}

// Теперь, если $logged_in_user не null, значит пользователь залогинен.
// Вы можете использовать $logged_in_user['username'] и $logged_in_user['id'].

?>