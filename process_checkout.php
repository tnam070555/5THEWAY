<?php
include 'includes/db_connect.php';
session_start();

/**
 * FILE: process_checkout.php
 * Chức năng: Lưu đơn hàng vào Database và hiển thị thông báo thành công
 */

// 1. Kiểm tra giỏ hàng - Nếu trống thì không cho xử lý
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// 2. Tiếp nhận dữ liệu từ form checkout.php
$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$phone    = mysqli_real_escape_string($conn, $_POST['phone']);
$email    = mysqli_real_escape_string($conn, $_POST['email']);
$city     = mysqli_real_escape_string($conn, $_POST['city']);
$district = mysqli_real_escape_string($conn, $_POST['district']);
$ward     = mysqli_real_escape_string($conn, $_POST['ward']);
$address_detail = mysqli_real_escape_string($conn, $_POST['address_detail']);
$note     = mysqli_real_escape_string($conn, $_POST['note']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

// Nối chuỗi địa chỉ đầy đủ
$full_address = $address_detail . ", " . $ward . ", " . $district . ", " . $city;

// 3. Tính toán tổng tiền đơn hàng (để lưu vào bảng HOADON)
$total_order = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $sql_price = "SELECT GiaNiemYet FROM SANPHAM WHERE MaSP = '$id'";
    $res_price = mysqli_query($conn, $sql_price);
    if ($row_price = mysqli_fetch_assoc($res_price)) {
        $total_order += ($row_price['GiaNiemYet'] * $qty);
    }
}

// 4. Thực hiện lưu vào Database
$ngay_lap = date('Y-m-d H:i:s');
$success = false;
$id_hoadon = 0;

// Bước 4.1: Chèn vào bảng HOADON
$sql_hd = "INSERT INTO HOADON (NgayLap, TongTien, HoTen, SDT, Email, DiaChi, GhiChu, PTThanhToan, TrangThai) 
           VALUES ('$ngay_lap', '$total_order', '$fullname', '$phone', '$email', '$full_address', '$note', '$payment_method', 'Chờ xử lý')";

if (mysqli_query($conn, $sql_hd)) {
    $id_hoadon = mysqli_insert_id($conn); // Lấy ID của hóa đơn vừa tạo

    // Bước 4.2: Chèn từng sản phẩm vào bảng CHITIETHOADON
    foreach ($_SESSION['cart'] as $id => $qty) {
        $sql_sp = "SELECT GiaNiemYet FROM SANPHAM WHERE MaSP = '$id'";
        $res_sp = mysqli_query($conn, $sql_sp);
        $row_sp = mysqli_fetch_assoc($res_sp);
        $gia = $row_sp['GiaNiemYet'];

        $sql_ct = "INSERT INTO CHITIETHOADON (MaHD, MaSP, SoLuong, DonGia) 
                   VALUES ('$id_hoadon', '$id', '$qty', '$gia')";
        mysqli_query($conn, $sql_ct);
    }
    
    $success = true;
    // Bước 4.3: Xóa giỏ hàng sau khi đã lưu xong xuôi
    unset($_SESSION['cart']);
}

// 5. Giao diện phản hồi người dùng
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng | 5THEWAY®</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fff; }
    </style>
</head>
<body>

<?php if ($success): ?>
    <script>
        Swal.fire({
            title: 'ĐẶT HÀNG THÀNH CÔNG!',
            html: 'Đơn hàng <b>#<?php echo $id_hoadon; ?></b> của bạn đang được hệ thống xử lý.<br>Cảm ơn bạn đã ủng hộ 5THEWAY®',
            icon: 'success',
            confirmButtonColor: '#000',
            confirmButtonText: 'TIẾP TỤC MUA SẮM',
            background: '#fff',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php';
            }
        });
    </script>
<?php else: ?>
    <script>
        Swal.fire({
            title: 'CÓ LỖI XẢY RA!',
            text: 'Không thể lưu đơn hàng. Vui lòng thử lại sau. Error: <?php echo mysqli_error($conn); ?>',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'QUAY LẠI GIỎ HÀNG'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cart.php';
            }
        });
    </script>
<?php endif; ?>

</body>
</html>
<?php
mysqli_close($conn);
?>