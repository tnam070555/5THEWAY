<?php
session_start();
$id = $_GET['id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'add'; // Mặc định là 'add' nếu không nói gì

if (isset($id)) {
    if ($action == 'add' || $action == 'increase') {
        // CỘNG THÊM
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
    } 
    elseif ($action == 'decrease') {
        // TRỪ BỚT
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;
            // Nếu trừ về 0 thì xóa luôn món đó
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    } 
    elseif ($action == 'remove') {
        // XÓA HẲN
        unset($_SESSION['cart'][$id]);
    }
}

// Xử lý xong thì quay về giỏ hàng
header("Location: cart.php");
exit();
?>