<?php
include("clstmdt.php");

class khachhang extends csdltmdt
{
    public function TinhGiaSauGiam($gia, $giamgia)
    {
        $gia = intval($gia);
        $giamgia = intval($giamgia);
        $ketqua = $gia - $giamgia;
        if ($ketqua < 0) {
            $ketqua = 0;
        }
        return $ketqua;
    }

public function XemDanhSachDonHangTheoTrangThai($trangthai = 'all')
{
    $conn = $this->connect();

    $sql = "SELECT dh.*, kh.hodem, kh.ten,
                   kh.diachinhanhang, kh.dienthoai
            FROM dathang dh
            LEFT JOIN khachhang kh ON dh.idkh = kh.idkh";

    if ($trangthai !== '' && $trangthai !== 'all') {
        $trangthai = mysqli_real_escape_string($conn, trim($trangthai));
        $sql .= " WHERE LOWER(TRIM(dh.trangthai)) = LOWER('$trangthai')";
    }

    $sql .= " ORDER BY dh.iddh DESC";

    $kq = $conn->query($sql);

    if (!$kq) {
        $sqlFallback = "SELECT * FROM dathang";

        if ($trangthai !== '' && $trangthai !== 'all') {
            $sqlFallback .= " WHERE LOWER(TRIM(trangthai)) = LOWER('$trangthai')";
        }

        $sqlFallback .= " ORDER BY iddh DESC";
        return $conn->query($sqlFallback);
    }

    return $kq;
}


    public function XemChiTietDonHang($iddh)
    {
        $conn = $this->connect();
        $iddh = intval($iddh);
        $sql = "SELECT ct.*, sp.ten, (ct.dongia - ct.giamgia) as giaban 
                FROM dathang_chitiet ct 
                LEFT JOIN sanpham sp ON ct.idsp = sp.idsp 
                WHERE ct.iddh = $iddh";
        return $conn->query($sql);
    }

    public function CapNhatTrangThaiDonHang($iddh, $trangthai)
    {
        $conn = $this->connect();
        $iddh = intval($iddh);
        $trangthai = mysqli_real_escape_string($conn, trim($trangthai));
        if ($iddh <= 0 || $trangthai === '') {
            return false;
        }
        $sql = "UPDATE dathang SET trangthai = '$trangthai' WHERE iddh = $iddh";
        return $conn->query($sql);
    }

    public function ThongKeDoanhThuTheoNgay()
    {
        $conn = $this->connect();
        $sql = "SELECT DATE(dh.ngaydatdathang) as ngay, SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                GROUP BY DATE(dh.ngaydatdathang) 
                ORDER BY ngay DESC";
        return $conn->query($sql);
    }

    public function ThongKeDoanhThuTheoThang()
    {
        $conn = $this->connect();
        $sql = "SELECT DATE_FORMAT(dh.ngaydatdathang, '%Y-%m') as thang, SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                GROUP BY thang 
                ORDER BY thang DESC";
        return $conn->query($sql);
    }

    public function ThongKeDoanhThuTheoNam()
    {
        $conn = $this->connect();
        $sql = "SELECT YEAR(dh.ngaydatdathang) as nam, SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                GROUP BY nam 
                ORDER BY nam DESC";
        return $conn->query($sql);
    }

    public function ThongKeSanPhamTheoThang()
    {
        $conn = $this->connect();
        $sql = "SELECT DATE_FORMAT(dh.ngaydatdathang, '%Y-%m') as thang, sp.ten, SUM(ct.soluong) as tongsoluong 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                JOIN sanpham sp ON ct.idsp = sp.idsp 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                GROUP BY thang, sp.idsp, sp.ten 
                ORDER BY thang DESC, tongsoluong DESC";
        return $conn->query($sql);
    }

    public function ThongKeSanPhamTheoNam()
    {
        $conn = $this->connect();
        $sql = "SELECT YEAR(dh.ngaydatdathang) as nam, sp.ten, SUM(ct.soluong) as tongsoluong 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                JOIN sanpham sp ON ct.idsp = sp.idsp 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                GROUP BY nam, sp.idsp, sp.ten 
                ORDER BY nam DESC, tongsoluong DESC";
        return $conn->query($sql);
    }
    public function TinhDoanhThuTheoNgayCuThe($ngay)
    {
        $conn = $this->connect();
        $ngay = mysqli_real_escape_string($conn, trim($ngay));
        $sql = "SELECT SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                AND DATE(dh.ngaydatdathang) = '$ngay'";
        $kq = $conn->query($sql);
        if ($kq && $row = $kq->fetch_assoc()) {
            return $row['doanhthu'] ? $row['doanhthu'] : 0;
        }
        return 0;
    }

    public function TinhDoanhThuTheoThangCuThe($thang)
    {
        $conn = $this->connect();
        $thang = mysqli_real_escape_string($conn, trim($thang));
        $sql = "SELECT SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                AND DATE_FORMAT(dh.ngaydatdathang, '%Y-%m') = '$thang'";
        $kq = $conn->query($sql);
        if ($kq && $row = $kq->fetch_assoc()) {
            return $row['doanhthu'] ? $row['doanhthu'] : 0;
        }
        return 0;
    }

    public function TinhDoanhThuTheoNamCuThe($nam)
    {
        $conn = $this->connect();
        $nam = intval($nam);
        $sql = "SELECT SUM(ct.soluong * (ct.dongia - ct.giamgia)) as doanhthu 
                FROM dathang dh 
                JOIN dathang_chitiet ct ON dh.iddh = ct.iddh 
                WHERE LOWER(TRIM(dh.trangthai)) NOT IN ('dahuy', 'đã hủy', 'da huy', 'đã huỷ') 
                AND YEAR(dh.ngaydatdathang) = $nam";
        $kq = $conn->query($sql);
        if ($kq && $row = $kq->fetch_assoc()) {
            return $row['doanhthu'] ? $row['doanhthu'] : 0;
        }
        return 0;
    }
    


    public function HienThiLuoiSanPham($tbl)
    {
        if ($tbl && $tbl->num_rows > 0) {
            echo '<div style="margin:30px; display:flex; flex-wrap:wrap;">';
            while ($r = $tbl->fetch_assoc()) {
                $giaSauGiam = $this->TinhGiaSauGiam($r['gia'], isset($r['giamgia']) ? $r['giamgia'] : 0);
                echo '<div style="width:30%; padding:8px; margin: 10px; text-align:center; box-shadow: 2px 5px 5px 2px rgb(182, 180, 180);">';
                echo '    <h4>' . htmlspecialchars($r['ten']) . '</h4>';
                echo '    <a href="chitietsanpham.php?id=' . urlencode($r['idsp']) . '">';
                echo '        <img src="style/images/' . htmlspecialchars($r['hinh']) . '" onerror="this.src=\'style/images/iphone11.png\'" style="width:100%; height:160px; object-fit:cover;">';
                echo '    </a>';
                if (intval($r['giamgia']) > 0) {
                    echo '    <p style="margin-top:6px;"><del>' . number_format($r['gia'], 0, ',', '.') . ' VND</del></p>';
                    echo '    <p style="padding-bottom:5px; color:red;"><strong>' . number_format($giaSauGiam, 0, ',', '.') . ' VND</strong></p>';
                } else {
                    echo '    <p style="padding-bottom:5px; color:red;"><strong>' . number_format($r['gia'], 0, ',', '.') . ' VND</strong></p>';
                }
                echo '    <a href="chitietsanpham.php?id=' . urlencode($r['idsp']) . '">Xem chi tiet</a>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p style="margin-top:50px; margin-left:50px;">Khong co san pham nao!</p>';
        }
    }

    public function XemDSAllSanPham()
    {
        $conn = $this->connect();
        $tbl = $this->SelectAllProduct($conn);
        $this->HienThiLuoiSanPham($tbl);
    }

    public function XemDSCongTy()
    {
        $conn = $this->connect();
        return $this->selectDataCty($conn);
    }

    public function XemSPByCty($idcty)
    {
        return $this->selectAllProductByCty($idcty);
    }

    public function XemAllCty()
    {
        return $this->selectAllCty();
    }

    public function XemChiTietSP($idsp)
    {
        return $this->selectChiTietSanPham($idsp);
    }

    public function XemSanPhamTheoDanhSachId($idList)
    {
        return $this->selectSanPhamByIds($idList);
    }

    public function DangNhapTaiKhoan($username, $password)
    {
        return $this->selectTaiKhoan($username, $password);
    }

    public function CapNhatLanDangNhapCuoi($iduser)
    {
        return $this->updateLanDangNhapCuoi($iduser);
    }

    public function DangKyTaiKhoan($username, $password, $hodem, $ten)
    {
        return $this->insertTaiKhoan($username, $password, $hodem, $ten, 'user');
    }

    public function DongBoKhachHangTuTaiKhoan($iduser, $hodem, $ten)
    {
        return $this->ensureKhachHang($iduser, $hodem, $ten);
    }

    public function LayThongTinKhachHang($iduser)
    {
        return $this->selectKhachHangById($iduser);
    }

    public function CapNhatDiaChiNhanHang($iduser, $diachi)
    {
        return $this->updateDiaChiNhanHang($iduser, $diachi);
    }

    public function DatHangTuGio($idkh, $giohang)
    {
        return $this->taoDonHang($idkh, $giohang);
    }

    public function XemDanhSachDonHang()
    {
        return $this->selectDanhSachDonHang();
    }

    public function XemDonHangTheoUser($iduser)
    {
        return $this->selectDonHangByUser($iduser);
    }

    public function XemDonHangTheoUserVaId($iduser, $iddh)
    {
        return $this->selectDonHangByUserAndId($iduser, $iddh);
    }

    public function ThongKeMuaHangTheoUser($iduser)
    {
        return $this->selectThongKeMuaHangByUser($iduser);
    }
}
?>