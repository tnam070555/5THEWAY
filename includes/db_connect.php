<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "shop_5theway"; // Tên database ông đã tạo

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Kết nối DB thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>