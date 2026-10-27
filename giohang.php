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
$diaChiNhanHang = "";

if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = array();
}

$gioHangOwner = isset($_SESSION['user']) ? ('user_' . intval($_SESSION['user']['iduser'])) : 'guest';
if (!isset($_SESSION['giohang_owner']) || $_SESSION['giohang_owner'] !== $gioHangOwner) {
    $_SESSION['giohang'] = array();
    $_SESSION['giohang_owner'] = $gioHangOwner;
    unset($_SESSION['last_order_id'], $_SESSION['last_order_time'], $_SESSION['last_order_user']);
}

if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    unset($_SESSION['user']);
    unset($_SESSION['last_order_id'], $_SESSION['last_order_time'], $_SESSION['last_order_user']);
    header('Location: giohang.php');
    exit;
}

if (isset($_GET['act']) && $_GET['act'] == 'xoa' && isset($_GET['id'])) {
    $idXoa = intval($_GET['id']);
    if (isset($_SESSION['giohang'][$idXoa])) {
        unset($_SESSION['giohang'][$idXoa]);
    }
    header('Location: giohang.php');
    exit;
}

if (isset($_GET['act']) && $_GET['act'] == 'xoatatca') {
    $_SESSION['giohang'] = array();
    header('Location: giohang.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnCapNhat'])) {
    $soLuongMoi = isset($_POST['soluong']) ? $_POST['soluong'] : array();
    foreach ($soLuongMoi as $idsp => $sl) {
        $idsp = intval($idsp);
        $sl = intval($sl);
        if ($sl <= 0) {
            unset($_SESSION['giohang'][$idsp]);
        } else {
            $_SESSION['giohang'][$idsp] = $sl;
        }
    }
    $thongbao = 'Da cap nhat gio hang.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnCapNhatDiaChi'])) {
    if (!isset($_SESSION['user'])) {
        $loi = 'Ban can dang nhap de cap nhat dia chi nhan hang.';
    } else {
        $iduser = intval($_SESSION['user']['iduser']);
        $hodem = isset($_SESSION['user']['hodem']) ? $_SESSION['user']['hodem'] : '';
        $ten = isset($_SESSION['user']['ten']) ? $_SESSION['user']['ten'] : '';
        $p->DongBoKhachHangTuTaiKhoan($iduser, $hodem, $ten);

        $diaChiNhanHangMoi = isset($_POST['diachinhanhang']) ? trim($_POST['diachinhanhang']) : '';
        if ($diaChiNhanHangMoi === '') {
            $loi = 'Vui long nhap dia chi nhan hang.';
        } else if ($p->CapNhatDiaChiNhanHang($iduser, $diaChiNhanHangMoi)) {
            $thongbao = 'Da cap nhat dia chi nhan hang.';
            $diaChiNhanHang = $diaChiNhanHangMoi;
        } else {
            $loi = 'Cap nhat dia chi nhan hang that bai.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDatHang'])) {
    if (!isset($_SESSION['user'])) {
        $loi = 'Ban phai dang nhap thanh cong moi duoc dat hang.';
    } else if (empty($_SESSION['giohang'])) {
        $loi = 'Gio hang dang trong, khong the dat hang.';
    } else {
        $iduser = intval($_SESSION['user']['iduser']);
        $hodem = isset($_SESSION['user']['hodem']) ? $_SESSION['user']['hodem'] : '';
        $ten = isset($_SESSION['user']['ten']) ? $_SESSION['user']['ten'] : '';

        $okKH = $p->DongBoKhachHangTuTaiKhoan($iduser, $hodem, $ten);
        if (!$okKH) {
            $loi = 'Khong tao duoc khach hang cho tai khoan hien tai.';
        } else {
            $diaChiDatHang = isset($_POST['diachinhanhang']) ? trim($_POST['diachinhanhang']) : '';
            if ($diaChiDatHang === '') {
                $thongTinKH = $p->LayThongTinKhachHang($iduser);
                if ($thongTinKH && isset($thongTinKH['diachinhanhang'])) {
                    $diaChiDatHang = trim($thongTinKH['diachinhanhang']);
                }
            }
            if ($diaChiDatHang === '') {
                $loi = 'Vui long cap nhat dia chi nhan hang truoc khi dat hang.';
            } else {
                $p->CapNhatDiaChiNhanHang($iduser, $diaChiDatHang);
                $diaChiNhanHang = $diaChiDatHang;
            }

            if ($loi !== '') {
                // giu nguyen gio hang de user cap nhat dia chi
            } else {
            $iddh = $p->DatHangTuGio($iduser, $_SESSION['giohang']);
            if ($iddh) {
                $_SESSION['last_order_id'] = $iddh;
                $_SESSION['last_order_time'] = date('Y-m-d H:i:s');
                $_SESSION['last_order_user'] = $iduser;
                $_SESSION['giohang'] = array();
                $thongbao = 'Dat hang thanh cong. Ma don hang: #' . $iddh;
            } else {
                $loi = 'Dat hang that bai. Vui long thu lai.';
            }
            }
        }
    }
}

if (isset($_SESSION['user']) && $diaChiNhanHang === '') {
    $iduser = intval($_SESSION['user']['iduser']);
    $thongTinKH = $p->LayThongTinKhachHang($iduser);
    if ($thongTinKH && isset($thongTinKH['diachinhanhang'])) {
        $diaChiNhanHang = trim($thongTinKH['diachinhanhang']);
    }
}

$soLuongGioHang = 0;
foreach ($_SESSION['giohang'] as $sl) {
    $soLuongGioHang += intval($sl);
}

$tblGioHang = false;
$mapSP = array();
if (!empty($_SESSION['giohang'])) {
    $tblGioHang = $p->XemSanPhamTheoDanhSachId(array_keys($_SESSION['giohang']));
    if ($tblGioHang && $tblGioHang->num_rows > 0) {
        while ($sp = $tblGioHang->fetch_assoc()) {
            $mapSP[$sp['idsp']] = $sp;
        }
    }
}

$dsVuaDat = false;
if (isset($_SESSION['user']) && isset($_SESSION['last_order_id']) && isset($_SESSION['last_order_user']) && intval($_SESSION['last_order_user']) === intval($_SESSION['user']['iduser'])) {
    $dsVuaDat = $p->XemChiTietDonHang($_SESSION['last_order_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/css/style.css">
    <title>Gio hang</title>
</head>
<body>
<div class="container">
    <div class="header"><h3>GIO HANG</h3></div>

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
                    echo '<a href="giohang.php?act=logout">Dang xuat</a>';
                    echo '</div>';
                } else { ?>
                    <div class="auth-links">
                        <a class="btn-auth" href="login.php?return=giohang.php">Dang nhap</a>
                        <a class="btn-auth" href="dangky.php?return=giohang.php">Dang ky</a>
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

        echo '<h3>Thong tin gio hang</h3>';
        if (empty($_SESSION['giohang'])) {
            echo '<p>Gio hang dang trong.</p>';
        } else {
            echo '<form method="post" action="giohang.php">';
            echo '<table style="width:100%; border-collapse:collapse;" border="1" cellpadding="8">';
            echo '<tr style="background:#f0f0f0;"><th>San pham</th><th>So luong</th><th>Don gia</th><th>Thanh tien</th><th></th></tr>';

            $tongTien = 0;
            foreach ($_SESSION['giohang'] as $id => $soluong) {
                if (!isset($mapSP[$id])) {
                    continue;
                }
                $giaSauGiam = $p->TinhGiaSauGiam($mapSP[$id]['gia'], $mapSP[$id]['giamgia']);
                $thanhTien = $giaSauGiam * intval($soluong);
                $tongTien += $thanhTien;

                echo '<tr>';
                echo '<td><a href="chitietsanpham.php?id=' . intval($id) . '">' . htmlspecialchars($mapSP[$id]['ten']) . '</a></td>';
                echo '<td style="text-align:center;"><input type="number" name="soluong[' . intval($id) . ']" value="' . intval($soluong) . '" min="0" style="width:70px;"></td>';
                echo '<td style="text-align:right;">' . number_format($giaSauGiam, 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:right; color:red;">' . number_format($thanhTien, 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:center;"><a href="giohang.php?act=xoa&id=' . intval($id) . '">Bo san pham</a></td>';
                echo '</tr>';
            }

            echo '<tr>';
            echo '<td colspan="3" style="text-align:right;"><strong>Tong tien</strong></td>';
            echo '<td style="text-align:right; color:red;"><strong>' . number_format($tongTien, 0, ',', '.') . ' VND</strong></td>';
            echo '<td></td>';
            echo '</tr>';
            echo '</table>';
            echo '<div style="margin-top:12px;">';
            if (isset($_SESSION['user'])) {
                echo '<div style="margin-bottom:10px;">';
                echo '<label><strong>Dia chi nhan hang:</strong></label><br>';
                echo '<input type="text" name="diachinhanhang" value="' . htmlspecialchars($diaChiNhanHang) . '" style="width:100%; max-width:500px; height:30px; padding:4px 6px;"> ';
                echo '<button type="submit" name="btnCapNhatDiaChi" style="height:30px; padding:0 10px; margin-left:6px;">Cap nhat dia chi</button>';
                echo '</div>';
            }
            echo '<input type="submit" name="btnCapNhat" value="Cap nhat so luong" style="height:30px;"> ';
            echo '<input type="submit" name="btnDatHang" value="Dat hang" style="height:30px;background:#d60000;color:#fff;border:none;padding:0 10px;"> ';
            echo '<a href="giohang.php?act=xoatatca" style="margin-left:10px;color:#c00;">Xoa toan bo</a> | ';
            echo '<a href="index.php">Dat them san pham</a>';
            echo '</div>';
            echo '</form>';
        }

        if ($dsVuaDat && $dsVuaDat->num_rows > 0) {
            echo '<h3 style="margin-top:30px;">Danh sach san pham vua dat (Don #' . intval($_SESSION['last_order_id']) . ')</h3>';
            echo '<table style="width:100%; border-collapse:collapse;" border="1" cellpadding="8">';
            echo '<tr style="background:#f8f8f8;"><th>San pham</th><th>So luong</th><th>Gia ban</th><th>Thanh tien</th></tr>';

            $tongDon = 0;
            while ($item = $dsVuaDat->fetch_assoc()) {
                $giaBan = intval($item['giaban']);
                if ($giaBan < 0) {
                    $giaBan = 0;
                }
                $thanhTien = $giaBan * intval($item['soluong']);
                $tongDon += $thanhTien;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($item['ten']) . '</td>';
                echo '<td style="text-align:center;">' . intval($item['soluong']) . '</td>';
                echo '<td style="text-align:right;">' . number_format($giaBan, 0, ',', '.') . ' VND</td>';
                echo '<td style="text-align:right; color:red;">' . number_format($thanhTien, 0, ',', '.') . ' VND</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="3" style="text-align:right;"><strong>Tong don vua dat</strong></td><td style="text-align:right; color:red;"><strong>' . number_format($tongDon, 0, ',', '.') . ' VND</strong></td></tr>';
            echo '</table>';
        }
        ?>
    </div>

    <div class="footer">LE TRONG PHUC</div>
</div>
</body>
</html>

