<?php
session_start();
include_once('../includes/db_connect.php');

if (!isset($_SESSION['user_admin'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['sid']) || empty($_GET['sid'])) {
    header('Location: manage_staff.php');
    exit();
}

$manv = mysqli_real_escape_string($conn, $_GET['sid']);

// Lấy thông tin nhân viên
$res_nv = mysqli_query($conn, "SELECT * FROM NHANVIEN WHERE MaNV = '$manv'");
$nv = mysqli_fetch_assoc($res_nv);

// Lấy danh sách lịch sử từ bảng LICHSU_HOATDONG
$sql_logs = "SELECT * FROM LICHSU_HOATDONG WHERE MaNV = '$manv' ORDER BY ThoiGian DESC LIMIT 50";
$res_logs = @mysqli_query($conn, $sql_logs);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>NHẬT KÝ HOẠT ĐỘNG | 5THEWAY®</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-black: #000; --bg-light: #f8f9fa; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; background: var(--bg-light); min-height: 100vh; }
        .sidebar { width: var(--sidebar-width); background: var(--primary-black); color: #fff; position: fixed; height: 100vh; padding: 30px 20px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; }
        
        .header-info { background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; border: 1px solid #eee; }
        .avatar-lg { width: 60px; height: 60px; background: #000; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; }
        
        .log-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #eee; }
        .log-table th { background: #fafafa; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; color: #999; }
        .log-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .time { color: #888; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2 style="text-align:center; margin-bottom:40px;">5THEWAY</h2>
        <a href="manage_staff.php" style="color:#fff; text-decoration:none; font-weight:700;"><i class="fa-solid fa-arrow-left"></i> QUAY LẠI</a>
    </aside>

    <main class="main-content">
        <h1 style="margin-bottom: 20px; font-weight: 900; text-transform: uppercase;">Lịch sử hoạt động</h1>

        <div class="header-info">
            <div class="avatar-lg"><?= strtoupper(substr($nv['HoTen'], 0, 1)) ?></div>
            <div>
                <h3 style="font-weight: 800;"><?= htmlspecialchars($nv['HoTen']) ?></h3>
                <p style="font-size: 13px; color: #666;">Chức vụ: <strong><?= $nv['Quyen'] ?></strong></p>
                <p style="font-size: 12px; color: #16a34a; font-weight: 600;">
                    Online cuối: <?= $nv['LastActive'] ? date('H:i | d/m/Y', strtotime($nv['LastActive'])) : 'Chưa ghi nhận' ?>
                </p>
            </div>
        </div>

        <table class="log-table">
            <thead>
                <tr>
                    <th style="width: 200px;">Thời gian</th>
                    <th>Hành động / Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_logs && mysqli_num_rows($res_logs) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($res_logs)): ?>
                    <tr>
                        <td class="time"><?= date('d/m/Y H:i:s', strtotime($row['ThoiGian'])) ?></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($row['HanhDong']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align:center; padding: 50px; color: #ccc;">Chưa có dữ liệu hoạt động.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>