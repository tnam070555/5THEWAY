<?php
include 'includes/db_connect.php';
session_start();

// 1. LẤY THÔNG SỐ BỘ LỌC TỪ URL
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : 'all';
$price_filter = isset($_GET['price']) ? $_GET['price'] : 'all';
$search_key = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP | 5THEWAY® STREETWEAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* --- CSS ĐỒNG NHẤT GIAO DIỆN --- */
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

        /* --- CSS DROPDOWN SHOP (HIỂN THỊ THEO CHIỀU DỌC) --- */
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
            list-style: none; /* Bỏ chấm tròn danh sách */
        }

        /* Lớp đệm để giữ hover không bị mất khi di chuyển chuột xuống menu */
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

        /* Quan trọng: Ép các mục li xếp theo chiều dọc */
        .dropdown-menu li { 
            display: block; 
            width: 100%; 
            text-align: left; 
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
        }

        .dropdown-menu li a:hover { 
            background: #f8f8f8 !important; 
            padding-left: 30px !important; 
        }

        /* --- LAYOUT TRANG SHOP --- */
        .shop-container { max-width: 1300px; margin: 40px auto; display: flex; gap: 50px; padding: 0 20px; }
        .shop-sidebar { width: 240px; flex-shrink: 0; position: sticky; top: 20px; height: fit-content; }
        .shop-main { flex-grow: 1; }

        .filter-group { margin-bottom: 35px; }
        .filter-title { 
            font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; display: block;
        }
        .filter-list { list-style: none; padding: 0; }
        .filter-list li { margin-bottom: 10px; }
        .filter-list a { text-decoration: none; color: #888; font-size: 13px; font-weight: 600; transition: 0.3s; text-transform: uppercase; }
        .filter-list a:hover, .filter-list a.active { color: #000; font-weight: 800; border-left: 3px solid #000; padding-left: 10px; }

        .price-opt { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #555; }
        .price-opt input { accent-color: #000; }

        .shop-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .shop-header h1 { font-size: 24px; font-weight: 900; text-transform: uppercase; }

        @media (max-width: 768px) { .shop-container { flex-direction: column; } .shop-sidebar { width: 100%; position: static; } }
    </style>
</head>

<body>

    <div class="top-bar">
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="assets/img/logo.png" alt="5THEWAY" class="logo-img"></a>
            </div>

            <div class="search-box">
                <form action="shop.php" method="GET">
                    <input type="text" name="search" placeholder="TÌM KIẾM SẢN PHẨM..." value="<?php echo htmlspecialchars($search_key); ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="header-icons">
                <a href="login.php" class="icon-link">
                    <div class="icon-wrap">
                        <?php if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])): ?>
                            <img src="uploads/avatars/<?php echo $_SESSION['user_avatar']; ?>" class="avatar-header-img">
                        <?php else: ?>
                            <i class="fa-regular fa-user"></i>
                        <?php endif; ?>
                    </div>
                </a>
                <a href="cart.php" class="icon-link">
                    <div class="icon-wrap cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="index.php">TRANG CHỦ</a></li>
            <li class="nav-dropdown">
                <a href="shop.php" class="active">SHOP <i class="fa-solid fa-chevron-down" style="font-size: 9px; margin-left: 5px;"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="shop.php?category=all">Tất cả sản phẩm</a></li>
                    <li><a href="shop.php?category=top">Tops (Áo)</a></li>
                    <li><a href="shop.php?category=bottom">Bottoms (Quần)</a></li>
                    <li><a href="shop.php?category=outerwear">Outerwear</a></li>
                    <li><a href="shop.php?category=accessories">Accessories</a></li>
                </ul>
            </li>
            <li><a href="#">SẢN PHẨM MỚI</a></li>
            <li><a href="#">EVENT</a></li>
            <li><a href="contact.php">LIÊN HỆ</a></li>
        </ul>
    </nav>

    <div class="shop-container">
        <aside class="shop-sidebar">
            <div class="filter-group">
                <span class="filter-title">DANH MỤC</span>
                <ul class="filter-list">
                    <li><a href="shop.php?category=all" class="<?php echo $category == 'all' ? 'active' : ''; ?>">TẤT CẢ</a></li>
                    <li><a href="shop.php?category=top" class="<?php echo $category == 'top' ? 'active' : ''; ?>">TOPS (ÁO)</a></li>
                    <li><a href="shop.php?category=bottom" class="<?php echo $category == 'bottom' ? 'active' : ''; ?>">BOTTOMS (QUẦN)</a></li>
                    <li><a href="shop.php?category=outerwear" class="<?php echo $category == 'outerwear' ? 'active' : ''; ?>">OUTERWEAR</a></li>
                    <li><a href="shop.php?category=accessories" class="<?php echo $category == 'accessories' ? 'active' : ''; ?>">ACCESSORIES</a></li>
                </ul>
            </div>

            <div class="filter-group">
                <span class="filter-title">MỨC GIÁ (VNĐ)</span>
                <form action="shop.php" method="GET">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <label class="price-opt">
                        <input type="radio" name="price" value="all" <?php echo $price_filter == 'all' ? 'checked' : ''; ?> onchange="this.form.submit()"> Tất cả
                    </label>
                    <label class="price-opt">
                        <input type="radio" name="price" value="0-300" <?php echo $price_filter == '0-300' ? 'checked' : ''; ?> onchange="this.form.submit()"> Dưới 300.000đ
                    </label>
                    <label class="price-opt">
                        <input type="radio" name="price" value="300-600" <?php echo $price_filter == '300-600' ? 'checked' : ''; ?> onchange="this.form.submit()"> 300k - 600k
                    </label>
                    <label class="price-opt">
                        <input type="radio" name="price" value="600-up" <?php echo $price_filter == '600-up' ? 'checked' : ''; ?> onchange="this.form.submit()"> Trên 600.000đ
                    </label>
                </form>
            </div>
        </aside>

        <main class="shop-main">
            <div class="shop-header">
                <h1>
                    <?php 
                        if($search_key) echo "KẾT QUẢ: " . htmlspecialchars($search_key);
                        else echo ($category == 'all') ? "CỬA HÀNG" : strtoupper($category);
                    ?>
                </h1>
                <span style="font-size: 11px; color: #bbb; font-weight: 800;">5THEWAY® GLOBAL</span>
            </div>

            <div class="product-grid">
                <?php
                $sql = "SELECT * FROM SANPHAM WHERE 1=1";
                if ($category != 'all') $sql .= " AND LoaiSP = '$category'";
                
                if ($price_filter == '0-300') $sql .= " AND GiaNiemYet < 300000";
                elseif ($price_filter == '300-600') $sql .= " AND GiaNiemYet BETWEEN 300000 AND 600000";
                elseif ($price_filter == '600-up') $sql .= " AND GiaNiemYet > 600000";
                
                if ($search_key) $sql .= " AND TenSP LIKE '%$search_key%'";
                
                $sql .= " ORDER BY MaSP DESC";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <div class="product-card">
                        <div class="product-img">
                            <a href="product_detail.php?id=<?php echo $row['MaSP']; ?>">
                                <img src="assets/img/<?php echo $row['HinhAnh']; ?>" onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'" alt="Product">
                            </a>
                        </div>
                        <div class="product-info">
                            <a href="product_detail.php?id=<?php echo $row['MaSP']; ?>" style="text-decoration: none; color: inherit;">
                                <h3><?php echo $row['TenSP']; ?></h3>
                            </a>
                            <p class="price"><?php echo number_format($row['GiaNiemYet'], 0, ',', '.'); ?>đ</p>
                            <a href="add_to_cart.php?action=add&id=<?php echo $row['MaSP']; ?>" class="btn-buy" style="display: block; text-align: center; text-decoration: none;">MUA NGAY</a>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align:center; padding: 50px;'>Không tìm thấy sản phẩm nào.</p>";
                }
                ?>
            </div>
        </main>
    </div>

</body>
</html>