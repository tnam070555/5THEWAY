<?php
session_start();
include 'includes/db_connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "success";

// 2. Xử lý cập nhật thông tin
if (isset($_POST['btn_update'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_image_sql = "";

    // Xử lý Upload Ảnh Đại Diện
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "uploads/avatars/";

        // Fix lỗi mkdir: Tạo thư mục từng cấp nếu chưa có
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allow_types = array('jpg', 'png', 'jpeg', 'webp');
        if (in_array(strtolower($file_extension), $allow_types)) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $update_image_sql = ", HinhAnh='$new_filename'";
            }
        } else {
            $msg = "Định dạng ảnh không hợp lệ (Chỉ nhận JPG, PNG, WEBP).";
            $msg_type = "error";
        }
    }

    if ($msg_type != "error") {
        // SỬA TẠI ĐÂY: Dùng cột SDT thay vì SoDienThoai cho đúng DB của ông
        $sql_update = "UPDATE KHACHHANG SET 
                        HoTen='$fullname', 
                        Email='$email', 
                        SDT='$phone', 
                        DiaChi='$address' 
                        $update_image_sql 
                       WHERE MaKH='$user_id'";

        if (mysqli_query($conn, $sql_update)) {
            $_SESSION['user_name'] = $fullname;
            // THÊM DÒNG NÀY: Cập nhật lại ảnh vào Session để Index thấy ngay
            if (isset($new_filename)) {
                $_SESSION['user_avatar'] = $new_filename;
            }
            $msg = "Cập nhật tài khoản thành công!";
        } else {
            $msg = "Lỗi Database: " . mysqli_error($conn);
            $msg_type = "error";
        }
    }
}

// 3. Lấy dữ liệu hiển thị
$sql = "SELECT * FROM KHACHHANG WHERE MaKH = '$user_id'";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);

// Đường dẫn ảnh
$avatar_path = (!empty($user['HinhAnh']) && file_exists("uploads/avatars/" . $user['HinhAnh']))
    ? "uploads/avatars/" . $user['HinhAnh']
    : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>HỒ SƠ CỦA TÔI | 5THEWAY GLOBAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #ffffff;
            --text: #000000;
            --gray: #f4f4f4;
            --dark-gray: #666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        /* Header chuyên nghiệp */
        .top-nav {
            padding: 20px 40px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 100;
        }

        .logo {
            font-weight: 900;
            letter-spacing: 5px;
            text-decoration: none;
            color: #000;
            font-size: 22px;
        }

        .home-link {
            text-decoration: none;
            color: #000;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            border: 2px solid #000;
            padding: 8px 20px;
            transition: 0.3s;
        }

        .home-link:hover {
            background: #000;
            color: #fff;
        }

        .container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 20px;
            display: flex;
            gap: 60px;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
        }

        .sidebar h2 {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .menu-list {
            list-style: none;
        }

        .menu-item {
            border-bottom: 1px solid #f0f0f0;
        }

        .menu-item a {
            display: block;
            padding: 16px 0;
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .menu-item a:hover,
        .menu-item a.active {
            color: #000;
            padding-left: 5px;
        }

        .menu-item a i {
            margin-right: 12px;
            width: 18px;
        }

        /* Content */
        .content {
            flex: 1;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 35px;
            margin-bottom: 45px;
            background: var(--gray);
            padding: 30px;
            border-radius: 8px;
        }

        .avatar-wrapper {
            position: relative;
            width: 110px;
            height: 110px;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #000;
            background: #fff;
        }

        .upload-hint {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #000;
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #fff;
        }

        .user-info-brief h3 {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .user-info-brief p {
            color: #888;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #999;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 15px;
            background: var(--gray);
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            background: #fff;
            border-color: #000;
        }

        .btn-save {
            background: #000;
            color: #fff;
            border: none;
            padding: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            margin-top: 30px;
            border-radius: 4px;
            width: 100%;
            transition: 0.3s;
        }

        .btn-save:hover {
            opacity: 0.8;
        }

        /* Msg */
        .msg {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            font-size: 13px;
            font-weight: 700;
            border-left: 5px solid;
        }

        .msg-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }

        .msg-error {
            background: #ffebee;
            color: #c62828;
            border-color: #c62828;
        }

        .student-tag {
            margin-top: 50px;
            font-size: 10px;
            color: #ccc;
            text-transform: uppercase;
            text-align: center;
        }
    </style>
</head>

<body>

    <header class="top-nav">
        <a href="index.php" class="logo">5THEWAY®</a>
        <a href="index.php" class="home-link"><i class="fa-solid fa-house"></i> Trang chủ</a>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h2>Tài khoản</h2>
            <ul class="menu-list">
                <li class="menu-item"><a href="profile.php" class="active"><i class="fa-solid fa-user"></i> Thông tin cá
                        nhân</a></li>
                <li class="menu-item"><a href="my_orders.php"><i class="fa-solid fa-box-open"></i> Đơn hàng của tôi</a>
                </li>
                <li class="menu-item"><a href="change-password.php"><i class="fa-solid fa-lock"></i> Đổi mật khẩu</a>
                </li>
                <li class="menu-item"><a href="logout.php" style="color:red;"><i class="fa-solid fa-power-off"></i> Đăng
                        xuất</a></li>
            </ul>
        </aside>

        <main class="content">
            <?php if ($msg != "")
                echo "<div class='msg msg-$msg_type'>$msg</div>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="profile-header">
                    <div class="avatar-wrapper">
                        <img src="<?php echo $avatar_path; ?>" alt="Avatar" class="avatar-img" id="preview-img">
                        <label for="avatar-input" class="upload-hint"><i class="fa-solid fa-camera"></i></label>
                        <input type="file" name="avatar" id="avatar-input" style="display:none;"
                            onchange="previewImage(this)">
                    </div>
                    <div class="user-info-brief">
                        <h3><?php echo htmlspecialchars($user['HoTen']); ?></h3>
                        <p>Thành viên của 5THEWAY </p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['TenDangNhap']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['HoTen']); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['SDT'] ?? ''); ?>">
                    </div>
                    <div class="form-group full">
                        <label>Địa chỉ giao hàng</label>
                        <input type="text" name="address"
                            value="<?php echo htmlspecialchars($user['DiaChi'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" name="btn_update" class="btn-save">Lưu hồ sơ</button>
            </form>
        </main>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) { document.getElementById('preview-img').src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>