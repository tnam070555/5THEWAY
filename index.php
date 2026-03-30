<?php
include 'includes/db_connect.php';
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>5THEWAY CLONE | Streetwear Brand</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Thêm một chút CSS để avatar bo tròn đẹp hơn trong Header */
        .avatar-header-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #000;
        }

        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- CHỈNH SỬA DROPDOWN SHOP --- */
        .nav-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(15px);
            background: #fff;
            min-width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 9999;
            padding: 10px 0;
            border-top: 3px solid #000;
            list-style: none;
            display: block; /* Đảm bảo hiển thị dạng khối */
        }

        /* Vùng đệm ngăn menu bị mất khi di chuyển chuột xuống */
        .nav-dropdown::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 20px;
        }

        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* Hiển thị danh sách theo hàng dọc */
        .dropdown-menu li {
            display: block;
            width: 100%;
            text-align: left;
            margin: 0;
        }

        .dropdown-menu li a {
            padding: 12px 25px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #000 !important;
            display: block;
            text-transform: uppercase;
            text-decoration: none;
            transition: 0.2s;
            border: none !important; /* Bỏ gạch chân mặc định nếu có */
        }

        .dropdown-menu li a:hover {
            background: #f8f8f8 !important;
            padding-left: 30px !important;
            color: #000 !important;
        }
        /* --- KẾT THÚC CHỈNH SỬA DROPDOWN --- */
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
                if (isset($_SESSION['user_admin']))
                    $target_link = 'admin/dashboard.php';
                elseif (isset($_SESSION['user_id']))
                    $target_link = 'profile.php';
                ?>
                <a href="<?php echo $target_link; ?>" class="icon-link">
                    <div class="icon-wrap">
                        <?php
                        if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists("uploads/avatars/" . $_SESSION['user_avatar'])): ?>
                            <img src="uploads/avatars/<?php echo $_SESSION['user_avatar']; ?>" alt="Avatar"
                                class="avatar-header-img">
                        <?php else: ?>
                            <i class="fa-regular fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="icon-text">
                        <span class="label">TÀI KHOẢN</span>
                        <span class="sub-label">
                            <?php
                            if (isset($_SESSION['user_admin'])) {
                                echo 'Admin';
                            } elseif (isset($_SESSION['user_name'])) {
                                $name_parts = explode(' ', $_SESSION['user_name']);
                                echo end($name_parts);
                            } else {
                                echo 'Đăng nhập';
                            }
                            ?>
                        </span>
                    </div>
                </a>

                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['user_admin'])): ?>
                    <a href="logout.php" class="icon-link" style="margin-left: 15px;">
                        <div class="icon-wrap" style="color: #ff4d4d;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <div class="icon-text">
                            <span class="label" style="color: #ff4d4d;">THOÁT</span>
                            <span class="sub-label">Logout</span>
                        </div>
                    </a>
                <?php endif; ?>

                <a href="cart.php" class="icon-link">
                    <div class="icon-wrap cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-count">
                            <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                        </span>
                    </div>
                    <div class="icon-text">
                        <span class="label">GIỎ HÀNG</span>
                        <span class="sub-label">
                            <?php
                            $total_price = 0;
                            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $id => $qty) {
                                    $sql_p = "SELECT GiaNiemYet FROM SANPHAM WHERE MaSP = '$id'";
                                    $res_p = mysqli_query($conn, $sql_p);
                                    if ($row_p = mysqli_fetch_assoc($res_p)) {
                                        $total_price += $row_p['GiaNiemYet'] * $qty;
                                    }
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

    <nav class="main-nav">
        <ul>
            <li><a href="index.php">TRANG CHỦ</a></li>
            
            <li class="nav-dropdown">
                <a href="shop.php">SHOP <i class="fa-solid fa-chevron-down" style="font-size: 9px; margin-left: 5px;"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="shop.php?category=all">Tất cả sản phẩm</a></li>
                    <li><a href="shop.php?category=top">Tops (Áo)</a></li>
                    <li><a href="shop.php?category=bottom">Bottoms (Quần)</a></li>
                    <li><a href="shop.php?category=outerwear">Outerwear</a></li>
                    <li><a href="shop.php?category=accessories">Accessories</a></li>
                </ul>
            </li>
            
            <li><a href="#">SẢN PHẨM MỚI</a></li>
            <li><a href="#">EVENT / SALE</a></li>
            <li><a href="#">LIÊN HỆ</a></li>
        </ul>
    </nav>

    <div class="hero-banner">
        <img src="assets/img/banner-main3.jpg" alt="Banner">
    </div>

    <div class="container">
        <h2 class="section-title">SẢN PHẨM NỔI BẬT</h2>

        <div class="product-grid">
            <?php
            $sql = "SELECT * FROM SANPHAM";
            if (isset($_GET['search'])) {
                $key = mysqli_real_escape_string($conn, $_GET['search']);
                $sql = "SELECT * FROM SANPHAM WHERE TenSP LIKE '%$key%'";
            } 
            else if (isset($_GET['category'])) {
                $category = mysqli_real_escape_string($conn, $_GET['category']);
                $sql = "SELECT * FROM SANPHAM WHERE LoaiSP = '$category'";
            }

            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="product-card">
                        <a href="product_detail.php?id=<?php echo $row['MaSP']; ?>"
                            style="text-decoration: none; color: inherit;">
                            <div class="product-img">
                                <img src="assets/img/<?php echo $row['HinhAnh']; ?>"
                                    onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'" alt="Product">
                            </div>
                        </a>

                        <div class="product-info">
                            <a href="product_detail.php?id=<?php echo $row['MaSP']; ?>"
                                style="text-decoration: none; color: inherit;">
                                <h3><?php echo $row['TenSP']; ?></h3>
                            </a>
                            <p class="price"><?php echo number_format($row['GiaNiemYet'], 0, ',', '.'); ?>đ</p>
                            <a href="add_to_cart.php?action=add&id=<?php echo $row['MaSP']; ?>" class="btn-buy"
                                style="display: block; text-align: center; text-decoration: none;">MUA NGAY</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align:center;'>Không tìm thấy sản phẩm nào.</p>";
            }
            ?>
        </div>
    </div>

    <footer>
    </footer>

</body>

</html>