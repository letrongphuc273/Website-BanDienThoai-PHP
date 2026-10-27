<?php
class csdltmdt
{
    public function connect()
    {
        $con = mysqli_connect("localhost", "root", "", "tmdt_db");
        if (!$con) {
            die("Loi ket noi CSDL: " . mysqli_connect_error());
        }
        mysqli_set_charset($con, "utf8");
        return $con;
    }

    public function dongketnoi($conn)
    {
        if ($conn) {
            $conn->close();
        }
    }

    public function selectDataCty($conn)
    {
        $query = "select tencty, diachi, dienthoai, fax from congty order by tencty asc";
        $kq = $conn->query($query);

        if ($kq && $kq->num_rows > 0) {
            echo '<h3 style="padding-top:20px; text-align:center;">DANH SACH CONG TY</h3>';
            echo '<table style="width:100%; margin-top:16px; border-collapse: collapse; text-align:center;" >
                    <tr style="background-color: rgb(165, 165, 165);">
                        <td style="width:50px; text-align:center;padding:5px;"><strong>STT</strong></td>
                        <td style="width:180px; text-align:center;"><strong>TEN CONG TY</strong></td>
                        <td style="width:205px; text-align:center;"><strong>DIA CHI</strong></td>
                        <td style="width:124px; text-align:center;"><strong>DIEN THOAI</strong></td>
                        <td style="width:124px; text-align:center;"><strong>FAX</strong></td>
                    </tr>';
            $i = 1;
            while ($thongtin = $kq->fetch_assoc()) {
                echo '<tr style="background-color:rgb(244, 243, 243);">
                        <td style="padding:5px;">' . $i . '</td>
                        <td>' . htmlspecialchars($thongtin["tencty"]) . '</td>
                        <td>' . htmlspecialchars($thongtin["diachi"]) . '</td>
                        <td>' . htmlspecialchars($thongtin["dienthoai"]) . '</td>
                        <td>' . htmlspecialchars($thongtin["fax"]) . '</td>
                      </tr>';
                $i++;
            }
            echo '</table>';
        } else {
            echo "Khong co du lieu";
        }
    }

    public function SelectAllProduct($conn)
    {
        $query = "select sp.idsp, sp.ten, sp.hinh, sp.gia, sp.giamgia, sp.mota, sp.idcty, cty.tencty
                  from sanpham sp
                  left join congty cty on sp.idcty = cty.idcty
                  order by sp.idsp desc";
        return $conn->query($query);
    }

    public function selectAllProductByCty($idcty)
    {
        $conn = $this->connect();
        $idcty = intval($idcty);

        $query = "select sp.idsp, sp.ten, sp.hinh, sp.gia, sp.giamgia, sp.mota, sp.idcty, cty.tencty
                  from sanpham sp
                  left join congty cty on sp.idcty = cty.idcty
                  where sp.idcty = $idcty
                  order by sp.idsp desc";
        return $conn->query($query);
    }

    public function selectAllCty()
    {
        $conn = $this->connect();
        $query = "select idcty, tencty from congty order by tencty asc";
        $kq = $conn->query($query);

        if (!$kq) {
            die("Loi truy van: " . $conn->error);
        }

        return $kq;
    }

    public function selectChiTietSanPham($idsp)
    {
        $conn = $this->connect();
        $idsp = intval($idsp);

        $sql = "select sp.idsp, sp.ten, sp.hinh, sp.gia, sp.giamgia, sp.mota, cty.tencty
                from sanpham sp
                join congty cty on sp.idcty = cty.idcty
                where sp.idsp = $idsp";
        return $conn->query($sql);
    }

    public function selectSanPhamByIds($idList)
    {
        if (!is_array($idList) || count($idList) === 0) {
            return false;
        }

        $ids = array();
        foreach ($idList as $id) {
            $id = intval($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (count($ids) === 0) {
            return false;
        }

        $conn = $this->connect();
        $idString = implode(',', $ids);
        $sql = "select idsp, ten, hinh, gia, giamgia from sanpham where idsp in ($idString)";
        return $conn->query($sql);
    }

    public function selectTaiKhoan($username, $password)
    {
        $conn = $this->connect();
        $username = mysqli_real_escape_string($conn, trim($username));
        $passwordMd5 = md5($password);

        $sql = "select iduser, username, hodem, ten, phanquyen, landangnhapcuoi
                from taikhoan
                where username = '$username' and password = '$passwordMd5'
                limit 1";

        $kq = $conn->query($sql);
        if ($kq && $kq->num_rows > 0) {
            return $kq->fetch_assoc();
        }
        return false;
    }

    public function updateLanDangNhapCuoi($iduser)
    {
        $conn = $this->connect();
        $iduser = intval($iduser);
        $sql = "update taikhoan set landangnhapcuoi = now() where iduser = $iduser";
        return $conn->query($sql);
    }

    public function insertTaiKhoan($username, $password, $hodem, $ten, $phanquyen = 'user')
    {
        $conn = $this->connect();
        $username = mysqli_real_escape_string($conn, trim($username));
        $hodem = mysqli_real_escape_string($conn, trim($hodem));
        $ten = mysqli_real_escape_string($conn, trim($ten));
        $phanquyen = mysqli_real_escape_string($conn, trim($phanquyen));

        if ($username === '' || trim($password) === '' || $ten === '') {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_.-]{4,30}$/', $username)) {
            return false;
        }

        if (strlen($password) < 6) {
            return false;
        }

        if ($phanquyen !== 'admin' && $phanquyen !== 'user') {
            $phanquyen = 'user';
        }

        $checkSql = "select iduser from taikhoan where username = '$username' limit 1";
        $check = $conn->query($checkSql);
        if ($check && $check->num_rows > 0) {
            return false;
        }

        $idMoi = 1;
        $kqMax = $conn->query("select max(iduser) as maxid from taikhoan");
        if ($kqMax && $kqMax->num_rows > 0) {
            $dong = $kqMax->fetch_assoc();
            $idMoi = intval($dong['maxid']) + 1;
            if ($idMoi <= 0) {
                $idMoi = 1;
            }
        }

        $passwordMd5 = md5($password);
        $sql = "insert into taikhoan(iduser, username, password, hodem, ten, phanquyen, landangnhapcuoi)
                values($idMoi, '$username', '$passwordMd5', '$hodem', '$ten', '$phanquyen', null)";

        return $conn->query($sql);
    }

    public function ensureKhachHang($idkh, $hodem, $ten)
    {
        $conn = $this->connect();
        $idkh = mysqli_real_escape_string($conn, (string)intval($idkh));
        $hodem = mysqli_real_escape_string($conn, trim((string)$hodem));
        $ten = mysqli_real_escape_string($conn, trim((string)$ten));

        $check = $conn->query("select idkh from khachhang where idkh = '$idkh' limit 1");
        if ($check && $check->num_rows > 0) {
            return true;
        }

        if ($ten === '') {
            $ten = 'KhachHang' . $idkh;
        }

        $sql = "insert into khachhang(idkh, email, password, hodem, ten, diachi, diachinhanhang, dienthoai)
                values('$idkh', '', '', '$hodem', '$ten', null, null, null)";
        return $conn->query($sql);
    }

    public function selectKhachHangById($idkh)
    {
        $conn = $this->connect();
        $idkh = mysqli_real_escape_string($conn, (string)intval($idkh));
        $sql = "select idkh, hodem, ten, diachi, diachinhanhang, dienthoai
                from khachhang
                where idkh = '$idkh'
                limit 1";
        $kq = $conn->query($sql);
        if ($kq && $kq->num_rows > 0) {
            return $kq->fetch_assoc();
        }
        return false;
    }

    public function updateDiaChiNhanHang($idkh, $diachinhanhang)
    {
        $conn = $this->connect();
        $idkh = mysqli_real_escape_string($conn, (string)intval($idkh));
        $diachinhanhang = mysqli_real_escape_string($conn, trim((string)$diachinhanhang));

        $sql = "update khachhang
                set diachinhanhang = '$diachinhanhang'
                where idkh = '$idkh'";
        return $conn->query($sql);
    }

    public function taoDonHang($idkh, $giohang)
    {
        if (!is_array($giohang) || count($giohang) === 0) {
            return false;
        }

        $idkh = intval($idkh);
        if ($idkh < 0) {
            return false;
        }

        $conn = $this->connect();
        $ids = array();
        foreach ($giohang as $idsp => $sl) {
            $idsp = intval($idsp);
            $sl = intval($sl);
            if ($idsp > 0 && $sl > 0) {
                $ids[] = $idsp;
            }
        }

        if (count($ids) === 0) {
            return false;
        }

        $idString = implode(',', $ids);
        $mapSP = array();
        $spResult = $conn->query("select idsp, gia, giamgia from sanpham where idsp in ($idString)");
        if ($spResult) {
            while ($sp = $spResult->fetch_assoc()) {
                $mapSP[intval($sp['idsp'])] = $sp;
            }
        }

        if (count($mapSP) === 0) {
            return false;
        }

        $conn->autocommit(false);

        $sqlDonHang = "insert into dathang(idkh, id_nhanvien, ngaydatdathang, thietlaplayngaygiaomacdinh, trangthai)
                      values('$idkh', null, now(), null, 'chuaxacnhan')";

        if (!$conn->query($sqlDonHang)) {
            $conn->rollback();
            $conn->autocommit(true);
            return false;
        }

        $iddh = $conn->insert_id;

        foreach ($giohang as $idsp => $sl) {
            $idsp = intval($idsp);
            $sl = intval($sl);
            if ($sl <= 0 || !isset($mapSP[$idsp])) {
                continue;
            }

            $dongia = floatval($mapSP[$idsp]['gia']);
            $giamgia = floatval($mapSP[$idsp]['giamgia']);

            $sqlCT = "insert into dathang_chitiet(iddh, idsp, soluong, dongia, giamgia)
                    values('$iddh', '$idsp', $sl, $dongia, $giamgia)";

            if (!$conn->query($sqlCT)) {
                $conn->rollback();
                $conn->autocommit(true);
                return false;
            }
        }

        $conn->commit();
        $conn->autocommit(true);
        return $iddh;
    }

    public function selectDonHangChiTiet($iddh)
    {
        $conn = $this->connect();
        $iddh = intval($iddh);
        $sql = "select ct.idsp, sp.ten, sp.hinh, ct.soluong, ct.dongia, ct.giamgia,
                       (ct.dongia - ct.giamgia) as giaban
                from dathang_chitiet ct
                join sanpham sp on ct.idsp = sp.idsp
                where ct.iddh = '$iddh'";
        return $conn->query($sql);
    }

    public function selectDanhSachDonHang()
    {
        $conn = $this->connect();
        $sql = "select dh.iddh, dh.idkh, dh.ngaydatdathang, dh.trangthai,
                       kh.hodem, kh.ten
                from dathang dh
                left join khachhang kh on dh.idkh = kh.idkh
                order by dh.iddh desc";
        return $conn->query($sql);
    }

    public function selectDonHangByUser($iduser)
    {
        $conn = $this->connect();
        $iduser = intval($iduser);
        $sql = "select dh.iddh, dh.idkh, dh.ngaydatdathang, dh.trangthai,
                       kh.hodem, kh.ten
                from dathang dh
                left join khachhang kh on dh.idkh = kh.idkh
                where dh.idkh = '$iduser'
                order by dh.iddh desc";
        return $conn->query($sql);
    }

    public function selectDonHangByUserAndId($iduser, $iddh)
    {
        $conn = $this->connect();
        $iduser = intval($iduser);
        $iddh = intval($iddh);
        $sql = "select dh.iddh, dh.idkh, dh.ngaydatdathang, dh.trangthai
                from dathang dh
                where dh.idkh = '$iduser' and dh.iddh = '$iddh'
                limit 1";
        $kq = $conn->query($sql);
        if ($kq && $kq->num_rows > 0) {
            return $kq->fetch_assoc();
        }
        return false;
    }

    public function selectThongKeMuaHangByUser($iduser)
    {
        $conn = $this->connect();
        $iduser = intval($iduser);

        $sqlTongQuan = "select
                            count(distinct dh.iddh) as tong_don,
                            ifnull(sum(ct.soluong), 0) as tong_sanpham,
                            ifnull(sum(ct.soluong * (ct.dongia - ct.giamgia)), 0) as tong_chitieu
                        from dathang dh
                        left join dathang_chitiet ct on dh.iddh = ct.iddh
                        where dh.idkh = '$iduser'";
        $kqTongQuan = $conn->query($sqlTongQuan);
        $tongQuan = array(
            'tong_don' => 0,
            'tong_sanpham' => 0,
            'tong_chitieu' => 0
        );
        if ($kqTongQuan && $kqTongQuan->num_rows > 0) {
            $dong = $kqTongQuan->fetch_assoc();
            $tongQuan['tong_don'] = intval($dong['tong_don']);
            $tongQuan['tong_sanpham'] = intval($dong['tong_sanpham']);
            $tongQuan['tong_chitieu'] = floatval($dong['tong_chitieu']);
        }

        $trangThai = array();
        $sqlTrangThai = "select trangthai, count(*) as so_don
                         from dathang
                         where idkh = '$iduser'
                         group by trangthai";
        $kqTrangThai = $conn->query($sqlTrangThai);
        if ($kqTrangThai && $kqTrangThai->num_rows > 0) {
            while ($r = $kqTrangThai->fetch_assoc()) {
                $trangThai[$r['trangthai']] = intval($r['so_don']);
            }
        }

        return array(
            'tong_quan' => $tongQuan,
            'trang_thai' => $trangThai
        );
    }

    public function updateTrangThaiDonHang($iddh, $trangthai)
    {
        $conn = $this->connect();
        $iddh = intval($iddh);
        $trangthai = mysqli_real_escape_string($conn, trim($trangthai));

        $hopLe = array('chuaxacnhan', 'daxacnhan', 'danggiao', 'dahuy');
        if (!in_array($trangthai, $hopLe, true)) {
            return false;
        }

        $sql = "update dathang set trangthai = '$trangthai' where iddh = $iddh";
        return $conn->query($sql);
    }
}
?>
