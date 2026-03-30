<?php 
include 'includes/db_connect.php'; 
session_start();

// 1. Lấy ID sản phẩm từ URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// 2. Truy vấn dữ liệu sản phẩm
$sql = "SELECT * FROM SANPHAM WHERE MaSP = '$id'";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['TenSP']; ?> | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* CHỈNH AVATAR NHỎ LẠI GIỐNG TRANG CHỦ */
        .avatar-header-img {
            width: 35px;  /* Kích thước giống index.php */
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #000;
        }

        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px; /* Khung chứa cũng thu nhỏ lại */
            height: 35px;
        }

        /* CSS CHO TRANG CHI TIẾT */
        .detail-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: flex;
            gap: 50px;
        }
        .detail-left { flex: 1.2; }
        .detail-left img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            border: 1px solid #eee;
        }
        .detail-right { flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .product-name { font-size: 32px; font-weight: 900; text-transform: uppercase; margin: 0; }
        .product-price { font-size: 24px; font-weight: 700; color: #000; }
        .option-label { font-size: 11px; font-weight: 900; text-transform: uppercase; color: #888; margin-bottom: 8px; display: block; }
        .option-box { display: flex; gap: 10px; margin-bottom: 15px; }
        .option-item { border: 1px solid #ddd; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .option-item.active { background: #000; color: #fff; border-color: #000; }
        .quantity-control { display: flex; align-items: center; border: 1px solid #ddd; width: fit-content; }
        .qty-btn { padding: 10px 15px; border: none; background: #fff; cursor: pointer; font-weight: bold; }
        .qty-input { width: 45px; text-align: center; border: none; font-weight: 700; font-size: 16px; }
        .buy-actions { display: flex; flex-direction: column; gap: 10px; }
        .btn-add { background: #fff; color: #000; border: 2px solid #000; padding: 18px; font-weight: 900; text-align: center; text-decoration: none; }
        .btn-buy-now { background: #000; color: #fff; border: 2px solid #000; padding: 18px; font-weight: 900; text-align: center; text-decoration: none; }

        /* CSS CHO PHẦN MÔ TẢ SẢN PHẨM MỚI THÊM */
        .product-description { 
            margin-top: 40px; 
            padding-top: 20px;
        }
        .desc-title { 
            font-size: 16px; 
            font-weight: 900; 
            text-transform: uppercase; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #000;
            display: inline-block;
            padding-bottom: 8px;
        }
        .desc-content { 
            font-size: 14px; 
            color: #444; 
            line-height: 1.8; 
            font-weight: 400;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="assets/img/logo.png" alt="5THEWAY" class="logo-img"></a>
            </div>

            <div class="search-box">
                <form action="index.php" method="GET">
                    <input type="text" name="search" placeholder="TÌM SẢN PHẨM..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="header-icons">
                <?php
                $target_link = 'login.php';
                if (isset($_SESSION['user_admin'])) $target_link = 'admin/dashboard.php';
                elseif (isset($_SESSION['user_id'])) $target_link = 'profile.php';
                ?>
                <a href="<?php echo $target_link; ?>" class="icon-link">
                    <div class="icon-wrap">
                        <?php if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists("uploads/avatars/" . $_SESSION['user_avatar'])): ?>
                            <img src="uploads/avatars/<?php echo $_SESSION['user_avatar']; ?>" alt="Avatar" class="avatar-header-img">
                        <?php else: ?>
                            <i class="fa-regular fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="icon-text">
                        <span class="label">TÀI KHOẢN</span>
                        <span class="sub-label">
                            <?php
                            if (isset($_SESSION['user_admin'])) echo 'Admin';
                            elseif (isset($_SESSION['user_name'])) {
                                $name_parts = explode(' ', $_SESSION['user_name']);
                                echo end($name_parts);
                            } else echo 'Đăng nhập';
                            ?>
                        </span>
                    </div>
                </a>

                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['user_admin'])): ?>
                    <a href="logout.php" class="icon-link" style="margin-left: 15px;">
                        <div class="icon-wrap" style="color: #ff4d4d;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                        <div class="icon-text">
                            <span class="label" style="color: #ff4d4d;">THOÁT</span>
                            <span class="sub-label">Logout</span>
                        </div>
                    </a>
                <?php endif; ?>

                <a href="cart.php" class="icon-link">
                    <div class="icon-wrap cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
                    </div>
                    <div class="icon-text">
                        <span class="label">GIỎ HÀNG</span>
                        <span class="sub-label">
                            <?php
                            $total_price = 0;
                            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $id_cart => $qty_cart) {
                                    $sql_p = "SELECT GiaNiemYet FROM SANPHAM WHERE MaSP = '$id_cart'";
                                    $res_p = mysqli_query($conn, $sql_p);
                                    if ($row_p = mysqli_fetch_assoc($res_p)) $total_price += $row_p['GiaNiemYet'] * $qty_cart;
                                }
                            }
                            echo number_format($total_price, 0, ',', '.') . 'đ';
                            ?>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-left">
            <img src="assets/img/<?php echo $product['HinhAnh']; ?>" onerror="this.src='https://via.placeholder.com/800x600?text=5THEWAY'">
            
            <div class="product-description">
                <div class="desc-title">Mô tả sản phẩm</div>
                <div class="desc-content">
                    <?php 
                        // Kiểm tra xem cột MoTa có dữ liệu không
                        if (isset($product['MoTa']) && trim($product['MoTa']) !== '') {
                            echo nl2br($product['MoTa']); // nl2br giúp giữ nguyên các dòng ngắt đoạn (enter) từ database
                        } else {
                            echo "Đang cập nhật thông tin chi tiết cho sản phẩm này...";
                        }
                    ?>
                </div>
            </div>
            </div>
        
        <div class="detail-right">
            <div>
                <h1 class="product-name"><?php echo $product['TenSP']; ?></h1>
                <div class="product-price"><?php echo number_format($product['GiaNiemYet'], 0, ',', '.'); ?>đ</div>
            </div>

            <div class="option-group">
                <span class="option-label">Màu sắc</span>
                <div class="option-box">
                    <div class="option-item active">BLACK</div>
                    <div class="option-item">WHITE</div>
                </div>
            </div>

            <div class="option-group">
                <span class="option-label">Kích thước</span>
                <div class="option-box">
                    <div class="option-item">S</div>
                    <div class="option-item active">M</div>
                    <div class="option-item">L</div>
                    <div class="option-item">XL</div>
                </div>
            </div>

            <div class="option-group">
                <span class="option-label">Số lượng</span>
                <div class="quantity-control">
                    <button class="qty-btn" onclick="updateQty(-1)">-</button>
                    <input type="text" id="p-qty" class="qty-input" value="1" readonly>
                    <button class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
            </div>

            <div class="buy-actions">
                <a href="add_to_cart.php?id=<?php echo $product['MaSP']; ?>" class="btn-add">THÊM VÀO GIỎ</a>
                <a href="checkout.php?id=<?php echo $product['MaSP']; ?>" class="btn-buy-now">MUA NGAY</a>
            </div>
        </div>
    </div>

    <script>
        function updateQty(step) {
            let input = document.getElementById('p-qty');
            let val = parseInt(input.value) + step;
            if (val >= 1) input.value = val;
        }
        document.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function() {
                this.parentElement.querySelector('.active').classList.remove('active');
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>