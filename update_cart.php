// add_to_cart.php
session_start();
$id = $_GET['id'];
if(isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]++;
} else {
    $_SESSION['cart'][$id] = 1;
}
header("Location: cart.php");