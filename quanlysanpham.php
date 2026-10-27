<?php
session_start();
include_once("classtmdt/clsquanlybh.php");
$q = new quanlybh();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['phanquyen']) || $_SESSION['user']['phanquyen'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$thongbao = "";
$loi = "";

$idspDangChon = $q->layIdSanPhamTuLink();
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if ($action === 'xoa' && $idspDangChon > 0) {
    if ($q->xoaSanPham($idspDangChon)) {
        header('Location: quanlysanpham.php?msg=deleted');
        exit;
    }
    $loi = 'Xoa san pham that bai.';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $thongbao = 'Da xoa san pham thanh cong.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idspForm = isset($_POST['idsp']) ? intval($_POST['idsp']) : 0;
    $idcty = isset($_POST['idcty']) ? intval($_POST['idcty']) : 0;
    $ten = isset($_POST['ten']) ? trim($_POST['ten']) : '';
    $gia = isset($_POST['gia']) ? intval($_POST['gia']) : 0;
    $giamgiaPhanTram = isset($_POST['giamgia_phantram']) ? floatval($_POST['giamgia_phantram']) : 0;
    $mota = isset($_POST['mota']) ? trim($_POST['mota']) : '';

    // Xử lý nút Xóa được bấm từ thanh hành động phía trên
    if (isset($_POST['btnXoa'])) {
        if ($idspForm <= 0) {
            $loi = 'Vui long chon san pham can xoa tu bang danh sach.';
        } else {
            if ($q->xoaSanPham($idspForm)) {
                header('Location: quanlysanpham.php?msg=deleted');
                exit;
            } else {
                $loi = 'Xoa san pham that bai.';
            }
        }
    }

    if ($giamgiaPhanTram < 0) {
        $giamgiaPhanTram = 0;
    }
    if ($giamgiaPhanTram > 100) {
        $loi = 'Giam gia (%) phai trong khoang 0-100.';
    }

    $giamgia = 0;
    if ($gia > 0) {
        $giamgia = intval(round($gia * $giamgiaPhanTram / 100));
    }
    if ($gia >= 0 && $giamgia > $gia) {
        $giamgia = $gia;
    }

    $hinh = '';
    if (isset($_FILES['hinh']) && isset($_FILES['hinh']['error'])) {
        if ($_FILES['hinh']['error'] === 0) {
            $tenGoc = basename($_FILES['hinh']['name']);
            $duoi = strtolower(pathinfo($tenGoc, PATHINFO_EXTENSION));
            $dinhDangHopLe = array('jpg', 'jpeg', 'png', 'gif', 'webp');

            if (!in_array($duoi, $dinhDangHopLe, true)) {
                $loi = 'Dinh dang hinh khong hop le. Chi chap nhan JPG, JPEG, PNG, GIF, WEBP.';
            } else {
                $tenMoi = 'sp_' . time() . '_' . mt_rand(1000, 9999) . '.' . $duoi;
                $thuMucHinh = dirname(__FILE__) . '/style/images';
                if (!is_dir($thuMucHinh)) {
                    @mkdir($thuMucHinh, 0777, true);
                }
                $duongDanLuu = $thuMucHinh . '/' . $tenMoi;
                if (is_uploaded_file($_FILES['hinh']['tmp_name']) && move_uploaded_file($_FILES['hinh']['tmp_name'], $duongDanLuu)) {
                    $hinh = $tenMoi;
                } else {
                    $loi = 'Khong thể upload hinh len server. Vui long thu lai.';
                }
            }
        } else if ($_FILES['hinh']['error'] !== UPLOAD_ERR_NO_FILE) {
            $loi = 'Upload hinh that bai. Ma loi: ' . intval($_FILES['hinh']['error']);
        }
    }

    if (isset($_POST['btnThem'])) {
        if ($hinh === '' && $loi === '') {
            $loi = 'Vui long chon hinh tu may khi them san pham moi.';
        }

        if ($loi === '' && $q->themSanPham($idcty, $ten, $gia, $giamgia, $mota, $hinh)) {
            header('Location: quanlysanpham.php?msg=added');
            exit;
        }
        if ($loi === '') {
            $loi = 'Them san pham that bai. Vui long kiem tra du lieu.';
        }
    }

    if (isset($_POST['btnSua'])) {
        if ($idspForm <= 0 && $loi === '') {
            $loi = 'Vui long chon san pham can sua tu bang danh sach.';
        } else {
            if ($loi === '' && $q->capNhatSanPham($idspForm, $idcty, $ten, $gia, $giamgia, $mota, $hinh)) {
                header('Location: quanlysanpham.php?msg=updated&idsp=' . $idspForm);
                exit;
            }
            if ($loi === '') {
                $loi = 'Sua san pham that bai. Vui long kiem tra du lieu.';
            }
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
    $thongbao = 'Da them san pham thanh cong.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $thongbao = 'Da cap nhat san pham thanh cong.';
}

$spDangChon = false;
if ($idspDangChon > 0) {
    $spDangChon = $q->layChiTietSanPham($idspDangChon);
}

$idctyForm = $spDangChon ? intval($spDangChon['idcty']) : 0;
$tenForm = $spDangChon ? $spDangChon['ten'] : '';
$giaForm = $spDangChon ? intval($spDangChon['gia']) : 0;
$giamgiaForm = $spDangChon ? intval($spDangChon['giamgia']) : 0;
$giamgiaPhanTramForm = 0;
if ($giaForm > 0) {
    $giamgiaPhanTramForm = round(($giamgiaForm / $giaForm) * 100, 2);
}
$motaForm = $spDangChon ? $spDangChon['mota'] : '';
$hinhCu = $spDangChon ? $spDangChon['hinh'] : '';

$dsCongTy = $q->layDanhSachCongTy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quan ly san pham</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", serif; background: #f1f1f1; margin: 0; padding: 20px; }
        .wrap { max-width: 1100px; margin: 0 auto; background: #fff; border: 1px solid #999; padding: 16px; }
        h2 { text-align: center; margin: 0 0 16px; }
        table.form-table { width: 100%; border-collapse: collapse; }
        .form-table td { border: 1px solid #999; padding: 8px; vertical-align: top; }
        .form-table .label { width: 180px; font-weight: bold; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 6px; }
        textarea { min-height: 110px; }
        .actions { margin-top: 10px; display: flex; gap: 8px; }
        .actions button { padding: 8px 12px; cursor: pointer; }
        .picked-id { margin-top: 8px; color: #0a5; font-weight: bold; }
        .msg-ok { color: #0a5; margin-bottom: 8px; }
        .msg-err { color: #c00; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="wrap">
    <h2>QUAN LY SAN PHAM</h2>
    <div style="margin-bottom:12px;">
        <a href="index.php" style="display:inline-block; padding:6px 10px; border:1px solid #777; text-decoration:none; background:#f7f7f7;">Thoat ve trang chu</a>
    </div>

    <?php if ($thongbao !== '') { echo '<div class="msg-ok">' . htmlspecialchars($thongbao) . '</div>'; } ?>
    <?php if ($loi !== '') { echo '<div class="msg-err">' . htmlspecialchars($loi) . '</div>'; } ?>

    <form method="post" action="quanlysanpham.php<?php echo $idspDangChon > 0 ? ('?idsp=' . $idspDangChon) : ''; ?>" enctype="multipart/form-data">
        <input type="hidden" name="idsp" value="<?php echo $idspDangChon; ?>">
        <table class="form-table">
            <tr>
                <td class="label">Cong ty cung cap</td>
                <td>
                    <select name="idcty" required>
                        <option value="">-Chon cong ty-</option>
                        <?php
                        if ($dsCongTy && $dsCongTy->num_rows > 0) {
                            while ($cty = $dsCongTy->fetch_assoc()) {
                                $selected = ($idctyForm === intval($cty['idcty'])) ? ' selected' : '';
                                echo '<option value="' . intval($cty['idcty']) . '"' . $selected . '>' . htmlspecialchars($cty['tencty']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">Nhap ten san pham</td>
                <td><input type="text" name="ten" value="<?php echo htmlspecialchars($tenForm); ?>" required></td>
            </tr>
            <tr>
                <td class="label">Nhap gia</td>
                <td><input type="number" name="gia" min="0" value="<?php echo htmlspecialchars((string)$giaForm); ?>" required></td>
            </tr>
            <tr>
                <td class="label">Nhap giam gia (%)</td>
                <td><input type="number" name="giamgia_phantram" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars((string)$giamgiaPhanTramForm); ?>" required></td>
            </tr>
            <tr>
                <td class="label">Mo ta</td>
                <td><textarea name="mota"><?php echo htmlspecialchars($motaForm); ?></textarea></td>
            </tr>
            <tr>
                <td class="label">Hinh dai dien</td>
                <td>
                    <input type="file" name="hinh" accept="image/*">
                    <?php if ($hinhCu !== '') { echo '<div style="margin-top:6px;">Hinh hien tai: ' . htmlspecialchars($hinhCu) . '</div>'; } ?>
                </td>
            </tr>
            
        </table>

        <div class="actions">
            <button type="submit" name="btnThem">Them san pham</button>
            <button type="submit" name="btnSua">Sua san pham</button>
            <button type="submit" name="btnXoa" onclick="return confirm('Ban co chac chan muon xoa san pham nay khong?');" >Xoa san pham</button>
        </div>
    </form>

    <?php
    if ($idspDangChon > 0) {
        echo '<div class="picked-id">ID san pham duoc lay tu duong link: ' . $idspDangChon . '</div>';
    }

    $q->danhsachsanpham("quanlysanpham.php");
    ?>
</div>
</body>
</html>