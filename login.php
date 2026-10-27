<?php
session_start();
include("classtmdt/clskhachhang.php");
$p = new khachhang();

function normalizeReturnUrl($url)
{
    $url = trim((string)$url);
    if ($url === '') {
        return 'index.php';
    }
    if (strpos($url, '://') !== false || strpos($url, '//') === 0) {
        return 'index.php';
    }
    if (strpos($url, "\n") !== false || strpos($url, "\r") !== false) {
        return 'index.php';
    }
    if ($url[0] === '/') {
        return 'index.php';
    }
    return $url;
}

$returnUrl = isset($_GET['return']) ? normalizeReturnUrl($_GET['return']) : 'index.php';
$loi = '';
$thongbao = '';

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $thongbao = 'Dang ky thanh cong. Vui long dang nhap.';
}

if (isset($_SESSION['user'])) {
    header('Location: ' . $returnUrl);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDangNhap'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $returnPost = isset($_POST['return']) ? normalizeReturnUrl($_POST['return']) : 'index.php';

    if ($username === '' || $password === '') {
        $loi = 'Vui long nhap day du username va password.';
    } else {
        $user = $p->DangNhapTaiKhoan($username, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            $p->CapNhatLanDangNhapCuoi($user['iduser']);
            header('Location: ' . $returnPost);
            exit;
        }
        $loi = 'Dang nhap that bai. Vui long kiem tra lai thong tin.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/css/style.css">
    <title>Dang nhap</title>
</head>
<body>
<div class="container" style="min-height: auto;">
    <div class="header"><h3>DANG NHAP</h3></div>
    <div style="padding:20px;">
        <?php if ($thongbao !== '') { ?>
            <p style="color:green; margin-bottom:12px;"><?php echo htmlspecialchars($thongbao); ?></p>
        <?php } ?>
        <?php if ($loi !== '') { ?>
            <p style="color:#c00; margin-bottom:12px;"><?php echo htmlspecialchars($loi); ?></p>
        <?php } ?>
        <form method="post" action="login.php?return=<?php echo urlencode($returnUrl); ?>" style="max-width:420px;">
            <input type="hidden" name="return" value="<?php echo htmlspecialchars($returnUrl); ?>">
            <p style="margin-bottom:10px;">
                <label>Username</label><br>
                <input type="text" name="username" style="width:100%; height:32px; padding:6px;">
            </p>
            <p style="margin-bottom:10px;">
                <label>Password</label><br>
                <input type="password" name="password" style="width:100%; height:32px; padding:6px;">
            </p>
            <p style="display:flex; gap:8px; flex-wrap:wrap;">
                <input type="submit" name="btnDangNhap" value="Dang nhap" style="height:32px; padding:0 12px;">
                <a class="btn-auth" href="dangky.php?return=<?php echo urlencode($returnUrl); ?>">Dang ky tai khoan moi</a>
                <a class="btn-auth" href="<?php echo htmlspecialchars($returnUrl); ?>">Quay lai</a>
            </p>
        </form>
    </div>
    <div class="footer">LE TRONG PHUC</div>
</div>
</body>
</html>
