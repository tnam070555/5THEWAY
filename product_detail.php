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
            width: 35px;
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
        .detail-left { flex: 1.2; display: flex; flex-direction: column; gap: 20px; }
        
        /* --- CSS MỚI CHO GALLERY VÀ ZOOM ẢNH --- */
        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .main-image-wrapper {
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid #eee;
            background: #f9f9f9;
            cursor: zoom-in;
            position: relative;
            border-radius: 8px; /* Bo góc nhẹ cho chuyên nghiệp */
        }

        .main-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease-out; /* Mượt mà khi nhả chuột */
        }

        .main-image-wrapper:hover img {
            transform: scale(2.2); /* Độ phóng to khi zoom */
            transition: none; /* Tắt transition khi di chuyển chuột để theo sát tọa độ */
        }

        .thumbnail-list {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        /* Tùy chỉnh thanh cuộn cho thumbnail */
        .thumbnail-list::-webkit-scrollbar { height: 5px; }
        .thumbnail-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        .thumb-item {
            width: 85px;
            height: 85px;
            border-radius: 6px;
            border: 2px solid transparent;
            overflow: hidden;
            cursor: pointer;
            opacity: 0.5;
            transition: all 0.3s ease;
            flex-shrink: 0;
            background: #f9f9f9;
        }

        .thumb-item:hover { opacity: 1; }
        .thumb-item.active { border-color: #000; opacity: 1; }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
        /* ------------------------------------- */

        .detail-right { flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .product-name { font-size: 32px; font-weight: 900; text-transform: uppercase; margin: 0; }
        .product-price { font-size: 24px; font-weight: 700; color: #000; }
        .option-label { font-size: 11px; font-weight: 900; text-transform: uppercase; color: #888; margin-bottom: 8px; display: block; }
        .option-box { display: flex; gap: 10px; margin-bottom: 15px; }
        .option-item { border: 1px solid #ddd; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .option-item.active { background: #000; color: #fff; border-color: #000; }
        .quantity-control { display: flex; align-items: center; border: 1px solid #ddd; width: fit-content; }
        .qty-btn { padding: 10px 15px; border: none; background: #fff; cursor: pointer; font-weight: bold; transition: 0.2s;}
        .qty-btn:hover { background: #f0f0f0; }
        .qty-input { width: 45px; text-align: center; border: none; font-weight: 700; font-size: 16px; }
        
        .buy-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 10px;}
        .btn-add { background: #fff; color: #000; border: 2px solid #000; padding: 18px; font-weight: 900; text-align: center; text-decoration: none; transition: 0.3s; }
        .btn-add:hover { background: #f5f5f5; }
        .btn-buy-now { background: #000; color: #fff; border: 2px solid #000; padding: 18px; font-weight: 900; text-align: center; text-decoration: none; transition: 0.3s; }
        .btn-buy-now:hover { background: #222; }

        /* CSS CHO PHẦN MÔ TẢ SẢN PHẨM */
        .product-description { 
            margin-top: 20px; 
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .detail-container { flex-direction: column; }
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
            <div class="product-gallery">
                <div class="main-image-wrapper" id="image-zoomer">
                    <img src="assets/img/<?php echo $product['HinhAnh']; ?>" id="main-img" onerror="this.src='https://via.placeholder.com/800x600?text=5THEWAY'">
                </div>
                
                <div class="thumbnail-list">
                    <div class="thumb-item active" onclick="changeMainImage(this, 'assets/img/<?php echo $product['HinhAnh']; ?>')">
                        <img src="assets/img/<?php echo $product['HinhAnh']; ?>" onerror="this.src='https://via.placeholder.com/80x80?text=5THEWAY'">
                    </div>
                    
                    <div class="thumb-item" onclick="changeMainImage(this, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&q=80&w=800')">
                        <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&q=80&w=150" alt="Góc khác">
                    </div>
                    <div class="thumb-item" onclick="changeMainImage(this, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&q=80&w=800')">
                        <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&q=80&w=150" alt="Chi tiết vải">
                    </div>
                    <div class="thumb-item" onclick="changeMainImage(this, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&q=80&w=800')">
                        <img src="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&q=80&w=150" alt="Mặt sau">
                    </div>
                </div>
            </div>
            
            <div class="product-description">
                <div class="desc-title">Mô tả chi tiết</div>
                <div class="desc-content">
                    <?php 
                        if (isset($product['MoTa']) && trim($product['MoTa']) !== '') {
                            echo nl2br($product['MoTa']); 
                        } else {
                            echo "The 5THEWAY® Global Edition. Chất liệu cotton 100% thoáng mát, form dáng oversized hiện đại phù hợp cho mọi hoạt động streetwear. Sản phẩm được thiết kế và hoàn thiện tỉ mỉ từng đường kim mũi chỉ.";
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
        // Xử lý tăng giảm số lượng
        function updateQty(step) {
            let input = document.getElementById('p-qty');
            let val = parseInt(input.value) + step;
            if (val >= 1) input.value = val;
        }

        // Đổi màu / size active
        document.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function() {
                this.parentElement.querySelector('.active').classList.remove('active');
                this.classList.add('active');
            });
        });

        // --- SCRIPT XỬ LÝ ZOOM VÀ ĐỔI ẢNH GALLERY ---
        const zoomer = document.getElementById('image-zoomer');
        const mainImg = document.getElementById('main-img');

        // Hàm tính tọa độ chuột và di chuyển tiêu điểm ảnh zoom
        zoomer.addEventListener('mousemove', function(e) {
            const { left, top, width, height } = zoomer.getBoundingClientRect();
            const x = (e.clientX - left) / width * 100;
            const y = (e.clientY - top) / height * 100;
            
            // Set tọa độ tâm của transform
            mainImg.style.transformOrigin = `${x}% ${y}%`;
        });

        // Trả ảnh về giữa khi chuột rời khỏi khung
        zoomer.addEventListener('mouseleave', function() {
            mainImg.style.transformOrigin = 'center center';
        });

        // Hàm đổi ảnh khi nhấn vào thumbnail
        function changeMainImage(element, newSrc) {
            // Thay đổi src của ảnh chính
            mainImg.src = newSrc;
            
            // Xóa active các thumbnail khác và thêm active cho ảnh vừa click
            document.querySelectorAll('.thumb-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
        }
    </script>
</body>
</html>