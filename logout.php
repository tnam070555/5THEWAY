<?php
session_start();
session_destroy(); // Xóa hết phiên làm việc
header("Location: index.php"); // Đá về trang chủ
exit();
?>