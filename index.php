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

if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    unset($_SESSION['user']);
    header('Location: index.php');
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

$soLuongGioHang = 0;
foreach ($_SESSION['giohang'] as $sl) {
    $soLuongGioHang += intval($sl);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/css/style.css">
    <title>Bai 6 - TMDT</title>
</head>

<body>
    <div class="container">
        <div class="header">
            <h3>BANNER</h3>
        </div>

        <div class="nav">
            <div class="nav-inner">
                <div class="nav-left">
                    <a href="index.php">Trang chu</a>
                    <span>|</span>
                    <a href="index.php?act=congty">Cong ty</a>
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
                        echo '<span>Xin chao, <strong>' . htmlspecialchars($hoTen) . '</strong> (' . htmlspecialchars($_SESSION['user']['phanquyen']) . ')</span>';
                        echo '<a href="index.php?act=logout">Dang xuat</a>';
                        echo '</div>';
                    } else { ?>
                    <div class="auth-links">
                        <a class="btn-auth" href="login.php?return=index.php">Dang nhap</a>
                        <a class="btn-auth" href="dangky.php?return=index.php">Dang ky</a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

 <div class="sectionhero" style="display:flex; align-items:stretch;">
    <div class="section" style="border-left:none; border-right:0.5px solid black;">
        <h3>MENU</h3>
        <?php
        $tbl = $p->XemAllCty();

        if ($tbl && $tbl->num_rows > 0) {
            echo "<ul>";
            echo "<li><a href='index.php'>Tat ca san pham</a></li>";

            while ($r = $tbl->fetch_assoc()) {
                echo "<li style='padding:6px; width:87%; margin-top:6px; margin-bottom:5px; margin-left:-8px;'>
                        <a href='index.php?type=" . urlencode($r['idcty']) . "' style='border-right:none;'>" . htmlspecialchars($r['tencty']) . "</a>
                      </li>";
            }

            echo "</ul>";
        } else {
            echo "<p style='padding:10px;'>Khong tim thay danh sach cong ty.</p>";
        }
        ?>
    </div>

    <div class="aside" style="border-left:0.5px solid black; border-right:none;">
        <?php
        if ($thongbao !== '') {
            echo '<p style="color:green; margin:20px;">' . htmlspecialchars($thongbao) . '</p>';
        }

        if (isset($_GET['type']) && is_numeric($_GET['type'])) {
            $idcty = intval($_GET['type']);
            $tblSP = $p->XemSPByCty($idcty);
            $p->HienThiLuoiSanPham($tblSP);
        } else if (isset($_GET['act']) && $_GET['act'] == "congty") {
            $conn = $p->connect();
            $p->selectDataCty($conn);
        } else {
            $p->XemDSAllSanPham();
        }
        ?>
    </div>

</div>
        <div class="footer">LE TRONG PHUC</div>
    </div>
</body>

</html>


 