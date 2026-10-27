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

if (!isset($_SESSION['user'])) {
    header('Location: login.php?return=donhangcuatoi.php');
    exit;
}

if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = array();
}

$gioHangOwner = 'user_' . intval($_SESSION['user']['iduser']);
if (!isset($_SESSION['giohang_owner']) || $_SESSION['giohang_owner'] !== $gioHangOwner) {
    $_SESSION['giohang'] = array();
    $_SESSION['giohang_owner'] = $gioHangOwner;
    unset($_SESSION['last_order_id'], $_SESSION['last_order_time'], $_SESSION['last_order_user']);
}

$soLuongGioHang = 0;
foreach ($_SESSION['giohang'] as $sl) {
    $soLuongGioHang += intval($sl);
}

$iduser = intval($_SESSION['user']['iduser']);
$iddhXem = isset($_GET['iddh']) ? intval($_GET['iddh']) : 0;
$donDangXem = false;
$ctDon = false;
$loi = '';
$thongKe = $p->ThongKeMuaHangTheoUser($iduser);

$dsDon = $p->XemDonHangTheoUser($iduser);
if ($iddhXem > 0) {
    $donDangXem = $p->XemDonHangTheoUserVaId($iduser, $iddhXem);
    if ($donDangXem) {
        $ctDon = $p->XemChiTietDonHang($iddhXem);
    } else {
        $loi = 'Khong tim thay don hang hoac ban khong co quyen xem don nay.';
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
    <title>Don hang cua toi</title>
</head>
<body>
<div class="container">
    <div class="header"><h3>DON HANG CUA TOI</h3></div>

    <div class="nav">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="index.php">Trang chu</a>
                <span>|</span>
                <a href="giohang.php">Gio hang (<?php echo $soLuongGioHang; ?>)</a>
                <span>|</span>
                <a href="donhangcuatoi.php">Don hang cua toi</a>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['phanquyen'] === 'admin') { ?>
                    <span>|</span>
                    <a href="quanlysanpham.php">Quan ly SP</a>
                    <span>|</span>
                    <a href="admin_quanly.php">Quan ly don</a>
                <?php } ?>
            </div>
            <div class="nav-right">
                <?php
                $hoTen = layTenHienThi($_SESSION['user']);
                echo '<div class="nav-user">';
                echo '<span>Xin chao, <strong>' . htmlspecialchars($hoTen) . '</strong></span>';
                echo '<a href="index.php?act=logout">Dang xuat</a>';
                echo '</div>';
                ?>
            </div>
        </div>
    </div>

    <div style="padding:20px; min-height:650px;">
        <?php if ($loi !== '') { echo '<p style="color:#c00;">' . htmlspecialchars($loi) . '</p>'; } ?>

        <?php
        $tongDon = isset($thongKe['tong_quan']['tong_don']) ? intval($thongKe['tong_quan']['tong_don']) : 0;
        $tongSanPham = isset($thongKe['tong_quan']['tong_sanpham']) ? intval($thongKe['tong_quan']['tong_sanpham']) : 0;
        $tongChiTieu = isset($thongKe['tong_quan']['tong_chitieu']) ? floatval($thongKe['tong_quan']['tong_chitieu']) : 0;
        $trangThaiThongKe = isset($thongKe['trang_thai']) ? $thongKe['trang_thai'] : array();
        ?>
        <h3>Thong ke lich su mua hang</h3>
        <p style="margin:8px 0 12px; font-size:16px;">
            <strong>Tong tien da mua (VND): <span style="color:#c00;"><?php echo number_format($tongChiTieu, 0, ',', '.'); ?> VND</span></strong>
        </p>
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" border="1" cellpadding="8">
            <tr style="background:#f8f8f8;">
                <th>Tong so don</th>
                <th>Tong so san pham da mua</th>
                <th>Tong chi tieu</th>
            </tr>
            <tr>
                <td style="text-align:center;"><?php echo $tongDon; ?></td>
                <td style="text-align:center;"><?php echo $tongSanPham; ?></td>
                <td style="text-align:right; color:#c00;"><?php echo number_format($tongChiTieu, 0, ',', '.'); ?> VND</td>
            </tr>
        </table>

        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" border="1" cellpadding="8">
            <tr style="background:#f8f8f8;">
                <th>Trang thai</th>
                <th>So don</th>
            </tr>
            <?php
            if (!empty($trangThaiThongKe)) {
                foreach ($trangThaiThongKe as $tt => $soDon) {
                    echo '<tr><td>' . htmlspecialchars($tt) . '</td><td style="text-align:center;">' . intval($soDon) . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2" style="text-align:center;">Chua co du lieu thong ke.</td></tr>';
            }
            ?>
        </table>

        <h3>Danh sach don hang</h3>
        <?php
        if ($dsDon && $dsDon->num_rows > 0) {
            echo '<table style="width:100%; border-collapse:collapse;" border="1" cellpadding="8">';
            echo '<tr style="background:#f0f0f0;"><th>Ma don</th><th>Ngay dat</th><th>Trang thai</th><th>Chi tiet</th></tr>';
            while ($don = $dsDon->fetch_assoc()) {
                echo '<tr>';
                echo '<td style="text-align:center;">#' . intval($don['iddh']) . '</td>';
                echo '<td style="text-align:center;">' . htmlspecialchars($don['ngaydatdathang']) . '</td>';
                echo '<td style="text-align:center;">' . htmlspecialchars($don['trangthai']) . '</td>';
                echo '<td style="text-align:center;"><a href="donhangcuatoi.php?iddh=' . intval($don['iddh']) . '">Xem</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Ban chua co don hang nao.</p>';
        }

        if ($donDangXem && $ctDon && $ctDon->num_rows > 0) {
            echo '<h3 style="margin-top:24px;">Chi tiet don #' . intval($donDangXem['iddh']) . '</h3>';
            echo '<table style="width:100%; border-collapse:collapse;" border="1" cellpadding="8">';
            echo '<tr style="background:#f8f8f8;"><th>San pham</th><th>So luong</th><th>Don gia</th><th>Giam gia</th><th>Gia ban</th><th>Thanh tien</th></tr>';

            $tong = 0;
            while ($ct = $ctDon->fetch_assoc()) {
                $giaBan = intval($ct['giaban']);
                if ($giaBan < 0) {
                    $giaBan = 0;
                }
                $thanhTien = $giaBan * intval($ct['soluong']);
                $tong += $thanhTien;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($ct['ten']) . '</td>';
                echo '<td style="text-align:center;">' . intval($ct['soluong']) . '</td>';
                echo '<td style="text-align:right;">' . number_format($ct['dongia'], 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:right;">' . number_format($ct['giamgia'], 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:right;">' . number_format($giaBan, 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:right; color:red;">' . number_format($thanhTien, 0, ',', '.') . ' VND</td>';
                echo '</tr>';
            }

            echo '<tr><td colspan="5" style="text-align:right;"><strong>Tong don</strong></td><td style="text-align:right; color:red;"><strong>' . number_format($tong, 0, ',', '.') . ' VND</strong></td></tr>';
            echo '</table>';
        }
        ?>
    </div>

    <div class="footer">LE TRONG PHUC</div>
</div>
</body>
</html>