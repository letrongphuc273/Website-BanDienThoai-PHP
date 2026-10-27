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
$loi = "";

if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = array();
}

$gioHangOwner = isset($_SESSION['user']) ? ('user_' . intval($_SESSION['user']['iduser'])) : 'guest';
if (!isset($_SESSION['giohang_owner']) || $_SESSION['giohang_owner'] !== $gioHangOwner) {
    $_SESSION['giohang'] = array();
    $_SESSION['giohang_owner'] = $gioHangOwner;
}

if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    unset($_SESSION['user']);
    header('Location: index.php');
    exit;
}

$isAdmin = isset($_SESSION['user']) && isset($_SESSION['user']['phanquyen']) && $_SESSION['user']['phanquyen'] === 'admin';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnCapNhatTrangThai'])) {
    $iddh = isset($_POST['iddh']) ? intval($_POST['iddh']) : 0;
    $trangthai = isset($_POST['trangthai']) ? trim($_POST['trangthai']) : '';

    if ($iddh > 0 && $trangthai !== '') {
        if ($p->CapNhatTrangThaiDonHang($iddh, $trangthai)) {
            $thongbao = 'Da cap nhat trang thai don #' . $iddh;
        } else {
            $loi = 'Cap nhat trang thai that bai.';
        }
    }
}

$soLuongGioHang = 0;
foreach ($_SESSION['giohang'] as $sl) {
    $soLuongGioHang += intval($sl);
}

$iddhXem = isset($_GET['iddh']) ? intval($_GET['iddh']) : 0;
$trangThaiLoc = isset($_GET['trangthai_loc']) ? trim($_GET['trangthai_loc']) : 'all';

// Xử lý tra cứu doanh thu
$loaiTk = isset($_GET['loai_tk']) ? trim($_GET['loai_tk']) : 'ngay';
$giaTriTk = isset($_GET['giatri_tk']) ? trim($_GET['giatri_tk']) : date('Y-m-d');
$ketQuaDoanhThu = null;

$dsDon = false;
$ctDon = false;

if ($isAdmin) {
    $dsDon = $p->XemDanhSachDonHangTheoTrangThai($trangThaiLoc);
    if ($iddhXem > 0) {
        $ctDon = $p->XemChiTietDonHang($iddhXem);
    }

    if ($loaiTk === 'ngay') {
        if ($giaTriTk === '') $giaTriTk = date('Y-m-d');
        $ketQuaDoanhThu = $p->TinhDoanhThuTheoNgayCuThe($giaTriTk);
    } elseif ($loaiTk === 'thang') {
        if ($giaTriTk === '') $giaTriTk = date('Y-m');
        $ketQuaDoanhThu = $p->TinhDoanhThuTheoThangCuThe($giaTriTk);
    } elseif ($loaiTk === 'nam') {
        if ($giaTriTk === '') $giaTriTk = date('Y');
        $ketQuaDoanhThu = $p->TinhDoanhThuTheoNamCuThe($giaTriTk);
    }

    $bieuDoThang = $p->ThongKeSanPhamTheoThang();
    $bieuDoNam = $p->ThongKeSanPhamTheoNam();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/css/style.css">
    <title>Admin - Quan ly don hang</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container" style="max-width: 1200px; margin: auto;">
    <div class="header"><h3>QUAN LY DON HANG</h3></div>

    <div class="nav">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="index.php">Trang chu</a>
                <span>|</span>
                <a href="giohang.php">Gio hang (<?php echo $soLuongGioHang; ?>)</a>
                <span>|</span>
                <a href="quanlysanpham.php">Quan ly SP</a>
                <span>|</span>
                <a href="admin_quanly.php">Quan ly don</a>
            </div>
            <div class="nav-right">
                <?php if (isset($_SESSION['user'])) {
                    $hoTen = layTenHienThi($_SESSION['user']);
                    echo '<div class="nav-user">';
                    echo '<span>Xin chao, <strong>' . htmlspecialchars($hoTen) . '</strong> (' . htmlspecialchars($_SESSION['user']['phanquyen']) . ')</span>';
                    echo '<a href="admin_quanly.php?act=logout">Dang xuat</a>';
                    echo '</div>';
                } else { ?>
                    <div class="auth-links">
                        <a class="btn-auth" href="login.php?return=admin_quanly.php">Dang nhap</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div style="padding:20px; min-height:650px;">
        <?php
        if ($loi !== '') {
            echo '<p style="color:#c00;">' . htmlspecialchars($loi) . '</p>';
        }
        if ($thongbao !== '') {
            echo '<p style="color:green;">' . htmlspecialchars($thongbao) . '</p>';
        }

        if (!$isAdmin) {
            echo '<p>Trang nay chi danh cho admin. Vui long dang nhap tai khoan admin de quan ly don hang.</p>';
        } else {
            ?>
            <div style="background: #f9f9f9; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd;">
                <form method="get" action="admin_quanly.php" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="loai_tk" value="<?php echo htmlspecialchars($loaiTk); ?>">
                    <input type="hidden" name="giatri_tk" value="<?php echo htmlspecialchars($giaTriTk); ?>">
                    
                    <label><strong>Loc don hang theo trang thai:</strong></label>
                    <select name="trangthai_loc" style="padding: 6px;">
                        <option value="all" <?php if($trangThaiLoc=='all') echo 'selected'; ?>>-- Tat ca trang thai --</option>
                        <option value="chuaxacnhan" <?php if($trangThaiLoc=='chuaxacnhan') echo 'selected'; ?>>chuaxacnhan</option>
                        <option value="daxacnhan" <?php if($trangThaiLoc=='daxacnhan') echo 'selected'; ?>>daxacnhan</option>
                        <option value="danggiao" <?php if($trangThaiLoc=='danggiao') echo 'selected'; ?>>danggiao</option>
                        <option value="dagiao" <?php if($trangThaiLoc=='dagiao') echo 'selected'; ?>>dagiao</option>
                        <option value="dahuy" <?php if($trangThaiLoc=='dahuy') echo 'selected'; ?>>dahuy</option>
                    </select>
                    <input type="submit" value="Loc don" style="padding: 6px 12px; cursor: pointer;">
                </form>
            </div>

            <h3>Danh sach don hang</h3>
            <?php
            if ($dsDon && $dsDon->num_rows > 0) {
                echo '<table style="width:100%; border-collapse:collapse; margin-bottom:20px;" border="1" cellpadding="8">';
                echo '<tr style="background:#f0f0f0;"><th>Ma don</th><th>Khach hang</th><th>Ngay dat</th><th>Trang thai</th><th>Cap nhat</th><th>Chi tiet</th></tr>';

                while ($don = $dsDon->fetch_assoc()) {
                    $hoDem = trim(isset($don['hodem']) ? $don['hodem'] : '');
$ten = trim(isset($don['ten']) ? $don['ten'] : '');

$hoTenKh = trim($hoDem . ' ' . $ten);

if ($hoTenKh === '') {
    $hoTenKh = 'IDKH ' . $don['idkh'];
}

                    echo '<tr>';
                    echo '<td style="text-align:center;">#' . intval($don['iddh']) . '</td>';
                    echo '<td>' . htmlspecialchars($hoTenKh) . '</td>';
                    echo '<td style="text-align:center;">' . htmlspecialchars($don['ngaydatdathang']) . '</td>';
                    echo '<td style="text-align:center;">' . htmlspecialchars($don['trangthai']) . '</td>';
                    echo '<td style="text-align:center;">';
                    echo '<form method="post" action="admin_quanly.php" style="display:flex; gap:6px; justify-content:center; align-items:center;">';
                    echo '<input type="hidden" name="iddh" value="' . intval($don['iddh']) . '">';
                    echo '<select name="trangthai" style="height:28px;">';
                    $trangThaiList = array('chuaxacnhan', 'daxacnhan', 'danggiao', 'dagiao', 'dahuy');
                    foreach ($trangThaiList as $tt) {
                        $selected = (trim($don['trangthai']) === $tt) ? ' selected' : '';
                        echo '<option value="' . $tt . '"' . $selected . '>' . $tt . '</option>';
                    }
                    echo '</select>';
                    echo '<input type="submit" name="btnCapNhatTrangThai" value="Luu" style="height:28px; cursor:pointer;">';
                    echo '</form>';
                    echo '</td>';
                    echo '<td style="text-align:center;"><a href="admin_quanly.php?iddh=' . intval($don['iddh']) . '">Xem</a></td>';
                    echo '</tr>';
                }

                echo '</table>';
            } else {
                echo '<p>Khong co don hang nao phu hop với trang thai này.</p>';
            }

            if ($ctDon && $ctDon->num_rows > 0) {
                echo '<h3 style="margin-top:24px;">Chi tiet don #' . $iddhXem . '</h3>';
                echo '<table style="width:100%; border-collapse:collapse; margin-bottom:30px;" border="1" cellpadding="8">';
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
                    echo '<td style="text-align:right;">' . number_format($thanhTien, 0, ',', '.') . ' VND</td>';
                    echo '</tr>';
                }

                echo '<tr><td colspan="5" style="text-align:right;"><strong>Tong don</strong></td><td style="text-align:right;"><strong>' . number_format($tong, 0, ',', '.') . ' VND</strong></td></tr>';
                echo '</table>';
            }
            ?>

           
<!-- BO LOC TRA CUU DOANH THU -->
<h3 style="margin-top:30px;">Tra cuu doanh thu</h3>

<div style="background:#f9f9f9; padding:20px; border:1px solid #ddd; margin-bottom:30px; border-radius:6px;">
    <form method="get" action="admin_quanly.php"
          style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">

        <input type="hidden" name="trangthai_loc"
               value="<?php echo htmlspecialchars($trangThaiLoc); ?>">

        <label><strong>Chon kieu thong ke:</strong></label>

        <select name="loai_tk" id="loai_tk"
                onchange="doiKieuNgay()" style="padding:8px;">
            <option value="ngay" <?php if ($loaiTk == 'ngay') echo 'selected'; ?>>Theo ngay</option>
            <option value="thang" <?php if ($loaiTk == 'thang') echo 'selected'; ?>>Theo thang</option>
            <option value="nam" <?php if ($loaiTk == 'nam') echo 'selected'; ?>>Theo nam</option>
        </select>

        <label><strong>Gia tri:</strong></label>

        <input type="<?php echo ($loaiTk == 'ngay') ? 'date' : (($loaiTk == 'thang') ? 'month' : 'number'); ?>"
               name="giatri_tk" id="giatri_tk"
               value="<?php echo htmlspecialchars($giaTriTk); ?>"
               <?php if ($loaiTk == 'nam') echo 'min="2000" max="2100"'; ?>
               style="padding:8px;" required>

        <input type="submit" value="Xem doanh thu"
               style="padding:8px 15px; background:#007bff; color:white; border:none; cursor:pointer; border-radius:3px;">
    </form>

    <div style="margin-top:20px; padding:18px; background:white; border:1px solid #ddd; border-radius:5px;">
        <div style="margin-bottom:10px; color:#555;">
            <?php
            if ($loaiTk == 'ngay') {
                echo 'Ket qua doanh thu ngay ' . htmlspecialchars($giaTriTk);
            } elseif ($loaiTk == 'thang') {
                echo 'Ket qua doanh thu thang ' . htmlspecialchars($giaTriTk);
            } else {
                echo 'Ket qua doanh thu nam ' . htmlspecialchars($giaTriTk);
            }
            ?>
        </div>

        <strong style="color:green; font-size:26px;">
            <?php echo number_format((float)$ketQuaDoanhThu, 0, ',', '.') . ' VND'; ?>
        </strong>
    </div>
</div>

<script>
function doiKieuNgay() {
    var loai = document.getElementById('loai_tk').value;
    var input = document.getElementById('giatri_tk');

    if (loai == 'ngay') {
        input.type = 'date';
        input.value = '<?php echo date('Y-m-d'); ?>';
        input.removeAttribute('min');
        input.removeAttribute('max');
    } else if (loai == 'thang') {
        input.type = 'month';
        input.value = '<?php echo date('Y-m'); ?>';
        input.removeAttribute('min');
        input.removeAttribute('max');
    } else {
        input.type = 'number';
        input.min = '2000';
        input.max = '2100';
        input.value = '<?php echo date('Y'); ?>';
    }
}
</script>
            <?php
        }
        ?>
    </div>

    <div class="footer">LE TRONG PHUC</div>
</div>
</body>
</html>