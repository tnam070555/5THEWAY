<?php
session_start();
include 'includes/db_connect.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIỎ HÀNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <style>
        /* CSS DÀNH RIÊNG CHO TRANG GIỎ HÀNG */
        .cart-wrapper { max-width: 1200px; margin: 50px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
        .cart-header-title { font-size: 28px; font-weight: 900; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .cart-header-title span { font-size: 14px; font-weight: 600; color: #666; }

        .cart-layout { display: flex; gap: 40px; align-items: flex-start; }
        
        /* CỘT TRÁI: DANH SÁCH SẢN PHẨM */
        .cart-list { flex: 7; }
        .cart-item { display: flex; gap: 20px; padding-bottom: 25px; margin-bottom: 25px; border-bottom: 1px solid #eee; position: relative; }
        .cart-item-img { width: 110px; height: 140px; background: #f5f5f5; border-radius: 4px; overflow: hidden; }
        .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .cart-item-details { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .cart-item-name { font-size: 15px; font-weight: 900; text-transform: uppercase; color: #000; text-decoration: none; margin-bottom: 8px; display: block; }
        .cart-item-variant { font-size: 12px; color: #888; font-weight: 600; }
        
        /* Nút tăng giảm số lượng */
        .qty-control { display: flex; align-items: center; border: 1px solid #ddd; width: fit-content; border-radius: 4px; overflow: hidden; margin-top: 15px; }
        .qty-btn { width: 35px; height: 35px; background: #fff; border: none; cursor: pointer; font-weight: 700; font-size: 16px; transition: 0.2s; }
        .qty-btn:hover { background: #f0f0f0; }
        .qty-input { width: 40px; height: 35px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; font-size: 14px; font-weight: 700; outline: none; pointer-events: none; }

        .cart-item-price { font-size: 16px; font-weight: 900; }
        .btn-remove { position: absolute; top: 0; right: 0; background: none; border: none; color: #aaa; cursor: pointer; font-size: 18px; transition: 0.2s; }
        .btn-remove:hover { color: #d00000; }

        /* CỘT PHẢI: TỔNG TIỀN */
        .cart-summary { flex: 4; background: #f9f9f9; padding: 30px; border-radius: 8px; position: sticky; top: 100px; }
        .summary-title { font-size: 16px; font-weight: 900; text-transform: uppercase; margin-bottom: 25px; }
        .summary-line { display: flex; justify-content: space-between; font-size: 14px; color: #555; margin-bottom: 15px; font-weight: 600; }
        .summary-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 900; color: #000; margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd; }
        
        .btn-checkout { width: 100%; padding: 18px; background: #000; color: #fff; border: none; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; border-radius: 4px; cursor: pointer; margin-top: 25px; transition: 0.3s; }
        .btn-checkout:hover { background: #333; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* Nút Quay lại trang chủ mới */
        .btn-back-home { width: 100%; display: block; text-align: center; padding: 15px; background: transparent; color: #000; border: 2px solid #000; font-size: 12px; font-weight: 900; text-transform: uppercase; border-radius: 4px; cursor: pointer; margin-top: 10px; text-decoration: none; transition: 0.3s; }
        .btn-back-home:hover { background: #000; color: #fff; }

        .empty-cart-msg { text-align: center; padding: 80px 0; }
        .empty-cart-msg i { font-size: 60px; color: #ddd; margin-bottom: 20px; }
        .empty-cart-msg h3 { font-size: 18px; font-weight: 900; text-transform: uppercase; margin-bottom: 15px; }
        .btn-continue { display: inline-block; padding: 12px 30px; border: 2px solid #000; color: #000; text-decoration: none; font-weight: 700; text-transform: uppercase; font-size: 12px; transition: 0.3s; }
        .btn-continue:hover { background: #000; color: #fff; }
    </style>
</head>
<body>

    <div class="cart-wrapper">
        <div class="cart-header-title">
            GIỎ HÀNG CỦA TÔI
            <span><?php echo array_sum($_SESSION['cart']); ?> SẢN PHẨM</span>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart-msg">
                <i class="fa-solid fa-cart-arrow-down"></i>
                <h3>Giỏ hàng đang trống</h3>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Bạn chưa chọn sản phẩm nào vào giỏ hàng.</p>
                <a href="index.php" class="btn-continue">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-list">
                    <?php 
                    foreach ($_SESSION['cart'] as $id => $qty): 
                        $sql = "SELECT * FROM SANPHAM WHERE MaSP = '$id'";
                        $res = mysqli_query($conn, $sql);
                        if ($res && mysqli_num_rows($res) > 0) {
                            $product = mysqli_fetch_assoc($res);
                            $subtotal = $product['GiaNiemYet'] * $qty;
                            $total_price += $subtotal;
                    ?>
                        <div class="cart-item">
                            <div class="cart-item-img">
                                <img src="assets/img/<?php echo $product['HinhAnh']; ?>" onerror="this.src='https://via.placeholder.com/110x140?text=No+Image'" alt="Product">
                            </div>
                            <div class="cart-item-details">
                                <div>
                                    <a href="product_detail.php?id=<?php echo $id; ?>" class="cart-item-name"><?php echo $product['TenSP']; ?></a>
                                    <span class="cart-item-variant">Size: Tùy chọn | Color: Mặc định</span>
                                </div>
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="window.location.href='add_to_cart.php?action=decrease&id=<?php echo $id; ?>'">-</button>
                                    <input type="text" class="qty-input" value="<?php echo $qty; ?>" readonly>
                                    <button class="qty-btn" onclick="window.location.href='add_to_cart.php?action=increase&id=<?php echo $id; ?>'">+</button>
                                </div>
                            </div>
                            <div class="cart-item-price">
                                <?php echo number_format($subtotal, 0, ',', '.'); ?>đ
                            </div>
                            <button class="btn-remove" onclick="if(confirm('Bạn muốn xóa sản phẩm này?')) window.location.href='add_to_cart.php?action=remove&id=<?php echo $id; ?>'" title="Xóa khỏi giỏ hàng">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    <?php 
                        }
                    endforeach; 
                    ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-title">Thông tin đơn hàng</div>
                    <div class="summary-line">
                        <span>Tạm tính (<?php echo array_sum($_SESSION['cart']); ?> món)</span>
                        <span><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="summary-line">
                        <span>Phí giao hàng</span>
                        <span>Được tính ở bước thanh toán</span>
                    </div>
                    <div class="summary-total">
                        <span>TỔNG CỘNG</span>
                        <span><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
                    </div>
                    
                    <button class="btn-checkout" onclick="window.location.href='checkout.php'">
                        Tiến hành thanh toán <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
                    </button>

                    <a href="index.php" class="btn-back-home">Tiếp tục mua hàng</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>