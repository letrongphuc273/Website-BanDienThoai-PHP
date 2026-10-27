<?php
session_start();
include("classtmdt/clskhachhang.php");
$p = new khachhang();

function layTenHienThi($user)
{
    $hoDem = trim(isset($user['hodem']) ? $user['hodem'] : '');
    $ten = trim(isset($user['ten']) ? $user['ten'] : '');

    if ($hoDem === '' && $ten === '') {
        return trim(isset($user['username']) ? $user['username'] : 'Nguoi dung');
    }
    if ($hoDem === '' || strcasecmp($hoDem, $ten) === 0) {
        return $ten !== '' ? $ten : $hoDem;
    }
    if ($ten === '') {
        return $hoDem;
    }
    return $hoDem . ' ' . $ten;
}

$thongbao = "";
$idsp = isset($_GET['id']) ? intval($_GET['id']) : 0;
$returnChiTiet = 'chitietsanpham.php?id=' . $idsp;

if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    unset($_SESSION['user']);
    header('Location: chitietsanpham.php?id=' . $idsp);
    exit;
}

if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = array();
}

$gioHangOwner = isset($_SESSION['user']) ? ('user_' . intval($_SESSION['user']['iduser'])) : 'guest';
if (!isset($_SESSION['giohang_owner']) || $_SESSION['giohang_owner'] !== $gioHangOwner) {
    $_SESSION['giohang'] = array();
    $_SESSION['giohang_owner'] = $gioHangOwner;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['sbGioHang']) || isset($_POST['sbDatHang']))) {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php?return=' . urlencode($returnChiTiet));
        exit;
    }

    $idspPost = isset($_POST['idsp']) ? intval($_POST['idsp']) : 0;
    $soluong = isset($_POST['soluong']) ? intval($_POST['soluong']) : 0;

    if ($idspPost > 0 && $soluong > 0) {
        if (isset($_SESSION['giohang'][$idspPost])) {
            $_SESSION['giohang'][$idspPost] += $soluong;
        } else {
            $_SESSION['giohang'][$idspPost] = $soluong;
        }

        if (isset($_POST['sbDatHang'])) {
            header('Location: giohang.php?act=checkout');
            exit;
        }

        header('Location: chitietsanpham.php?id=' . $idspPost . '&msg=added');
        exit;
    }
    $thongbao = 'So luong phai lon hon 0.';
}

if (isset($_GET['msg']) && $_GET['msg'] == 'added') {
    $thongbao = 'Da them san pham vao gio hang.';
}

$soLuongGioHang = 0;
foreach ($_SESSION['giohang'] as $sl) {
    $soLuongGioHang += intval($sl);
}

$chiTiet = false;
if ($idsp > 0) {
    $chiTiet = $p->XemChiTietSP($idsp);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/css/style.css">
    <title>Chi tiet san pham</title>
</head>

<body>
    <div class="container">
        <div class="header">
            <h3>CHI TIET SAN PHAM</h3>
        </div>

        <div class="nav">
            <div class="nav-inner">
                <div class="nav-left">
                <a href="index.php">Trang chu</a>
                <span>|</span>
                <a href="giohang.php">Gio hang (<?php echo $soLuongGioHang; ?>)</a>
                <?php if (isset($_SESSION['user'])) { ?>
                    <span>|</span>
                    <a href="donhangcuatoi.php">Don hang cua toi</a>
                <?php } ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['phanquyen'] === 'admin') { ?>
                    <span>|</span>
                    <a href="quanlysanpham.php">Quan ly SP</a>
                    <span>|</span>
                    <a href="admin_quanly.php">Quan ly don</a>
                    <?php } ?>
                </div>
                <div class="nav-right">
                    <?php if (isset($_SESSION['user'])) {
                    $hoTen = layTenHienThi($_SESSION['user']);
                    echo '<div class="nav-user">';
                    echo '<span>Xin chao, <strong>' . htmlspecialchars($hoTen) . '</strong></span>';
                    echo '<a href="chitietsanpham.php?id=' . $idsp . '&act=logout">Dang xuat</a>';
                    echo '</div>';
                } else { ?>
                    <div class="auth-links">
                        <a class="btn-auth" href="login.php?return=<?php echo urlencode($returnChiTiet); ?>">Dang
                            nhap</a>
                        <a class="btn-auth" href="dangky.php?return=<?php echo urlencode($returnChiTiet); ?>">Dang
                            ky</a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div style="padding:20px; min-height:650px;">
            <?php
        if ($thongbao !== '') {
            echo '<p style="color:green;">' . htmlspecialchars($thongbao) . '</p>';
        }

        if ($chiTiet && $chiTiet->num_rows > 0) {
            $r = $chiTiet->fetch_assoc();
            $giaSauGiam = $p->TinhGiaSauGiam($r['gia'], isset($r['giamgia']) ? $r['giamgia'] : 0);

            echo '<table style="width:100%;">';
            echo '<tr>
                    <td style="width:45%; text-align:center;">
                        <img src="style/images/' . htmlspecialchars($r['hinh']) . '" onerror="this.src=\'style/images/iphone11.png\'" style="width:300px; max-width:100%; height:auto; object-fit:cover;">
                    </td>
                    <td style="padding-right:15px; vertical-align:top;">
                        <p><strong>' . htmlspecialchars($r['ten']) . '</strong></p><br>
                        <p><strong>Cong ty: </strong>' . htmlspecialchars($r['tencty']) . '</p><br>
                        <p><strong>Mo ta: </strong>' . htmlspecialchars($r['mota']) . '</p><br>';

            if (intval($r['giamgia']) > 0) {
                echo '<p><strong>Gia goc:</strong> <del>' . number_format($r['gia'], 0, ',', '.') . ' VND</del></p><br>';
                echo '<p><strong>Giam gia:</strong> ' . number_format($r['giamgia'], 0, ',', '.') . ' VND</p><br>';
                echo '<p><strong>Gia ban:</strong> <span style="color:red;">' . number_format($giaSauGiam, 0, ',', '.') . ' VND</span></p><br>';
            } else {
                echo '<p><strong>Gia:</strong> <span style="color:red;">' . number_format($r['gia'], 0, ',', '.') . ' VND</span></p><br>';
            }

            echo '  <form method="post" action="chitietsanpham.php?id=' . intval($r['idsp']) . '">
                        <input type="hidden" name="idsp" value="' . intval($r['idsp']) . '">
                        <p><strong>So luong: </strong><input type="number" name="soluong" value="1" min="1" style="width:70px;height:28px;"></p><br>
                        <input type="submit" name="sbGioHang" value="Them vao gio hang" style="height:30px;background-color:green; border:none;color:white; padding:0 10px;">
                        <input type="submit" name="sbDatHang" value="Dat hang ngay" style="height:30px;background-color:red; border:none;color:white; padding:0 10px;">
                    </form>
                    <br><a href="index.php">Quay lai danh sach san pham</a>
                    </td>
                  </tr>';
            echo '</table>';
        } else {
            echo '<p>Khong tim thay san pham.</p>';
        }
        ?>
        </div>

        <div class="footer">LE TRONG PHUC</div>
    </div>
</body>

</html>
