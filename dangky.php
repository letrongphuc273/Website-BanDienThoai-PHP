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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDangKy'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $repassword = isset($_POST['repassword']) ? trim($_POST['repassword']) : '';
    $hodem = isset($_POST['hodem']) ? trim($_POST['hodem']) : '';
    $ten = isset($_POST['ten']) ? trim($_POST['ten']) : '';
    $returnPost = isset($_POST['return']) ? normalizeReturnUrl($_POST['return']) : 'index.php';

    if ($username === '' || $password === '' || $ten === '') {
        $loi = 'Vui long nhap day du thong tin bat buoc.';
    } else if (!preg_match('/^[A-Za-z0-9_.-]{4,30}$/', $username)) {
        $loi = 'Username chi duoc chua chu, so, dau ., _, - va dai 4-30 ky tu.';
    } else if (strlen($password) < 6) {
        $loi = 'Password phai tu 6 ky tu tro len.';
    } else if ($password !== $repassword) {
        $loi = 'Nhap lai password khong khop.';
    } else {
        if ($p->DangKyTaiKhoan($username, $password, $hodem, $ten)) {
            header('Location: login.php?return=' . urlencode($returnPost) . '&registered=1');
            exit;
        }
        $loi = 'Dang ky that bai (co the username da ton tai).';
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
    <title>Dang ky tai khoan</title>
</head>
<body>
<div class="container" style="min-height: auto;">
    <div class="header"><h3>DANG KY TAI KHOAN MOI</h3></div>
    <div style="padding:20px;">
        <?php if ($loi !== '') { ?>
            <p style="color:#c00; margin-bottom:12px;"><?php echo htmlspecialchars($loi); ?></p>
        <?php } ?>
        <form method="post" action="dangky.php?return=<?php echo urlencode($returnUrl); ?>" style="max-width:460px;">
            <input type="hidden" name="return" value="<?php echo htmlspecialchars($returnUrl); ?>">
            <p style="margin-bottom:10px;">
                <label>Username *</label><br>
                <input type="text" name="username" style="width:100%; height:32px; padding:6px;" required>
            </p>
            <p style="margin-bottom:10px;">
                <label>Password *</label><br>
                <input type="password" name="password" style="width:100%; height:32px; padding:6px;" required>
            </p>
            <p style="margin-bottom:10px;">
                <label>Nhap lai password *</label><br>
                <input type="password" name="repassword" style="width:100%; height:32px; padding:6px;" required>
            </p>
            <p style="margin-bottom:10px;">
                <label>Ho dem</label><br>
                <input type="text" name="hodem" style="width:100%; height:32px; padding:6px;">
            </p>
            <p style="margin-bottom:10px;">
                <label>Ten *</label><br>
                <input type="text" name="ten" style="width:100%; height:32px; padding:6px;" required>
            </p>
            <p style="display:flex; gap:8px; flex-wrap:wrap;">
                <input type="submit" name="btnDangKy" value="Dang ky" style="height:32px; padding:0 12px;">
                <a class="btn-auth" href="login.php?return=<?php echo urlencode($returnUrl); ?>">Toi trang dang nhap</a>
                <a class="btn-auth" href="<?php echo htmlspecialchars($returnUrl); ?>">Quay lai</a>
            </p>
        </form>
    </div>
    <div class="footer">LE TRONG PHUC</div>
</div>
</body>
</html>
