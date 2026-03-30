<?php 
include 'includes/db_connect.php'; 
session_start();

// 1. Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Giỏ hàng của bạn đang trống!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Tính toán đơn hàng
$total_all = 0;
$cart_items = [];
foreach ($_SESSION['cart'] as $id => $qty) {
    $id_safe = mysqli_real_escape_string($conn, $id);
    $sql = "SELECT * FROM SANPHAM WHERE MaSP = '$id_safe'";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        $row['qty'] = $qty;
        $row['subtotal'] = $row['GiaNiemYet'] * $qty;
        $total_all += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THANH TOÁN | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --primary-black: #000; --border-color: #ddd; --text-gray: #555; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: var(--primary-black); margin: 0; }
        .checkout-wrapper { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 60px; }
        .checkout-title { font-size: 22px; font-weight: 900; text-transform: uppercase; margin-bottom: 30px; letter-spacing: 1px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 11px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; color: var(--text-gray); }
        .form-group label span { color: #ff0000; margin-left: 3px; }
        .form-control { width: 100%; padding: 15px; border: 1px solid var(--border-color); font-size: 14px; outline: none; transition: 0.3s; box-sizing: border-box; }
        .form-control:focus { border-color: var(--primary-black); }
        .address-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .payment-methods { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
        .payment-item { display: flex; align-items: center; padding: 18px; border: 1px solid var(--border-color); cursor: pointer; transition: 0.3s; }
        .payment-item:hover { background: #f9f9f9; border-color: #000; }
        .payment-item input { margin-right: 15px; accent-color: #000; width: 18px; height: 18px; }
        .payment-info { display: flex; align-items: center; gap: 12px; flex: 1; }
        .payment-info i { font-size: 18px; }
        .payment-name { font-weight: 700; font-size: 13px; text-transform: uppercase; }
        .payment-icons { margin-left: auto; display: flex; gap: 8px; font-size: 22px; color: #444; }
        .order-summary { background: #f9f9f9; padding: 30px; border: 1px solid #eee; position: sticky; top: 120px; }
        .product-list-mini { border-bottom: 1px solid #ddd; margin-bottom: 20px; max-height: 350px; overflow-y: auto; }
        .p-mini-item { display: flex; gap: 15px; margin-bottom: 15px; align-items: center; }
        .p-mini-img { width: 65px; height: 65px; object-fit: cover; border: 1px solid #eee; }
        .p-mini-info h4 { font-size: 12px; font-weight: 900; text-transform: uppercase; margin: 0; line-height: 1.4; }
        .p-mini-info p { font-size: 12px; color: #888; margin: 3px 0; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .total-row { border-top: 2px solid #000; padding-top: 20px; display: flex; justify-content: space-between; font-size: 18px; font-weight: 900; }
        .btn-confirm { width: 100%; background: #000; color: #fff; padding: 20px; border: none; font-weight: 900; text-transform: uppercase; cursor: pointer; margin-top: 30px; transition: 0.3s; letter-spacing: 1px; }
        .btn-confirm:hover { background: #333; }
        @media (max-width: 768px) { .checkout-wrapper { grid-template-columns: 1fr; } .address-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="assets/img/logo.png" alt="5THEWAY" class="logo-img"></a>
            </div>
            <div class="header-icons">
                <a href="cart.php" class="icon-link">
                    <div class="icon-wrap cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
                    </div>
                    <div class="icon-text">
                        <span class="label">GIỎ HÀNG</span>
                        <span class="sub-label"><?php echo number_format($total_all, 0, ',', '.'); ?>đ</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="checkout-wrapper">
        <div class="checkout-form-container">
            <h2 class="checkout-title">Thông tin giao hàng</h2>
            <form action="process_checkout.php" method="POST" id="main-checkout-form">
                <div class="form-group">
                    <label>Họ và tên <span>*</span></label>
                    <input type="text" name="fullname" class="form-control" placeholder="Nhập họ tên người nhận" required>
                </div>

                <div class="address-row">
                    <div class="form-group">
                        <label>Số điện thoại <span>*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="09xxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span>*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="nguyenvana@gmail.com" required>
                    </div>
                </div>

                <div class="address-row">
                    <div class="form-group">
                        <label>Tỉnh / Thành phố <span>*</span></label>
                        <select class="form-control" id="city" name="city" required>
                            <option value="" selected disabled>Chọn tỉnh thành</option>           
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quận / Huyện <span>*</span></label>
                        <select class="form-control" id="district" name="district" required>
                            <option value="" selected disabled>Chọn quận huyện</option>
                        </select>
                    </div>
                </div>

                <div class="address-row">
                    <div class="form-group">
                        <label>Phường / Xã <span>*</span></label>
                        <select class="form-control" id="ward" name="ward" required>
                            <option value="" selected disabled>Chọn phường xã</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số nhà, tên đường <span>*</span></label>
                        <input type="text" name="address_detail" class="form-control" placeholder="Ví dụ: 273 An Dương Vương" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ghi chú đơn hàng</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                </div>
                
                <h2 class="checkout-title" style="margin-top: 40px;">Phương thức thanh toán</h2>
                <div class="payment-methods">
                    <label class="payment-item">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <div class="payment-info">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span class="payment-name">Thanh toán khi nhận hàng (COD)</span>
                        </div>
                    </label>

                    <label class="payment-item">
                        <input type="radio" name="payment_method" value="ATM_VISA">
                        <div class="payment-info">
                            <i class="fa-solid fa-credit-card"></i>
                            <span class="payment-name">Thẻ nội địa ATM / VISA / MASTER</span>
                            <div class="payment-icons">
                                <i class="fa-brands fa-cc-visa"></i>
                                <i class="fa-brands fa-cc-mastercard"></i>
                            </div>
                        </div>
                    </label>

                    <label class="payment-item">
                        <input type="radio" name="payment_method" value="MOMO">
                        <div class="payment-info">
                            <i class="fa-solid fa-wallet"></i>
                            <span class="payment-name">Ví điện tử MoMo / ZaloPay</span>
                        </div>
                    </label>
                </div>
            </form>
        </div>

        <div class="order-summary">
            <h2 class="checkout-title" style="font-size: 18px; border-bottom: 1px solid #ddd;">Đơn hàng của bạn</h2>
            
            <div class="product-list-mini">
                <?php foreach ($cart_items as $item): ?>
                <div class="p-mini-item">
                    <img src="assets/img/<?php echo $item['HinhAnh']; ?>" class="p-mini-img">
                    <div class="p-mini-info">
                        <h4><?php echo $item['TenSP']; ?></h4>
                        <p>Số lượng: <?php echo $item['qty']; ?></p>
                        <p style="font-weight: 700; color: #000;"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?>đ</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-item">
                <span>Tạm tính</span>
                <span><?php echo number_format($total_all, 0, ',', '.'); ?>đ</span>
            </div>
            <div class="summary-item">
                <span>Phí vận chuyển</span>
                <span style="color: #27ae60; font-weight: 700;">MIỄN PHÍ</span>
            </div>
            
            <div class="total-row">
                <span>TỔNG CỘNG</span>
                <span><?php echo number_format($total_all, 0, ',', '.'); ?>đ</span>
            </div>

            <button type="submit" form="main-checkout-form" class="btn-confirm">HOÀN TẤT ĐẶT HÀNG</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script>
        const host = "https://provinces.open-api.vn/api/";
        var callAPI = (api) => {
            return axios.get(api)
                .then((response) => {
                    renderData(response.data, "city");
                });
        }
        callAPI('https://provinces.open-api.vn/api/?depth=1');
        var callApiDistrict = (api) => {
            return axios.get(api)
                .then((response) => {
                    renderData(response.data.districts, "district");
                });
        }
        var callApiWard = (api) => {
            return axios.get(api)
                .then((response) => {
                    renderData(response.data.wards, "ward");
                });
        }

        var renderData = (array, select) => {
            let row = ' <option disable value="" selected>Chọn</option>';
            array.forEach(element => {
                row += `<option value="${element.name}" data-id="${element.code}">${element.name}</option>`
            });
            document.querySelector("#" + select).innerHTML = row;
        }

        document.querySelector("#city").addEventListener("change", () => {
            let cityCode = document.querySelector("#city option:checked").dataset.id;
            callApiDistrict(host + "p/" + cityCode + "?depth=2");
        });
        document.querySelector("#district").addEventListener("change", () => {
            let districtCode = document.querySelector("#district option:checked").dataset.id;
            callApiWard(host + "d/" + districtCode + "?depth=2");
        });
    </script>
</body>
</html>