<?php
$servername = "localhost"; // Replace with your database server name
$database = "databasename"; // Replace with your database name
$username = "username"; // Replace with your database username
$password = "password"; // Replace with your database password

// Создаем соединение
$conn = mysqli_connect($servername, $username, $password, $database);

// Проверяем соединение
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully using MySQLi";

mysqli_close($conn);
?>