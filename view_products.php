<?php
require_once 'mysqli_databaseconnect.php';

// Проверяем соединение (уже сделано в mysqli_databaseconnect.php, но можно добавить дополнительную проверку)
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$sql = "SELECT id, name, price, short_description FROM product";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Список товаров</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Список товаров</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Краткое описание</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        // Вывод данных каждой строки
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["price"] . "</td>";
            echo "<td>" . $row["short_description"] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Нет товаров в базе данных.</td></tr>";
    }
    ?>

</table>

<?php
$conn->close();
?>

</body>
</html>