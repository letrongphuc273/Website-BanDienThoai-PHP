<?php
include_once("clstmdt.php");

class quanlybh extends csdltmdt
{
    public function layIdSanPhamTuLink($param = "idsp")
    {
        if (isset($_GET[$param]) && is_numeric($_GET[$param])) {
            return intval($_GET[$param]);
        }
        return 0;
    }

    public function layDanhSachCongTy()
    {
        $conn = $this->connect();
        $sql = "select idcty, tencty from congty order by tencty asc";
        return $conn->query($sql);
    }

    public function layChiTietSanPham($idsp)
    {
        $conn = $this->connect();
        $idsp = intval($idsp);
        $sql = "select idsp, ten, gia, giamgia, mota, idcty, hinh from sanpham where idsp = $idsp limit 1";
        $kq = $conn->query($sql);
        if ($kq && $kq->num_rows > 0) {
            return $kq->fetch_assoc();
        }
        return false;
    }

    public function themSanPham($idcty, $ten, $gia, $giamgia, $mota, $hinh)
    {
        $conn = $this->connect();
        $idcty = intval($idcty);
        $ten = mysqli_real_escape_string($conn, trim($ten));
        $gia = intval($gia);
        $giamgia = intval($giamgia);
        $mota = mysqli_real_escape_string($conn, trim($mota));
        $hinh = mysqli_real_escape_string($conn, trim($hinh));

        if ($idcty <= 0 || $ten === '' || $gia < 0 || $giamgia < 0 || $giamgia > $gia) {
            return false;
        }

        $sql = "insert into sanpham(ten, gia, mota, idcty, giamgia, hinh)
                values('$ten', $gia, '$mota', $idcty, $giamgia, '$hinh')";
        return $conn->query($sql);
    }

    public function capNhatSanPham($idsp, $idcty, $ten, $gia, $giamgia, $mota, $hinhMoi = "")
    {
        $conn = $this->connect();
        $idsp = intval($idsp);
        $idcty = intval($idcty);
        $ten = mysqli_real_escape_string($conn, trim($ten));
        $gia = intval($gia);
        $giamgia = intval($giamgia);
        $mota = mysqli_real_escape_string($conn, trim($mota));

        if ($idsp <= 0 || $idcty <= 0 || $ten === '' || $gia < 0 || $giamgia < 0 || $giamgia > $gia) {
            return false;
        }

        $setHinh = "";
        if (trim($hinhMoi) !== "") {
            $hinhMoi = mysqli_real_escape_string($conn, trim($hinhMoi));
            $setHinh = ", hinh = '$hinhMoi'";
        }

        $sql = "update sanpham
                set ten = '$ten', gia = $gia, giamgia = $giamgia, mota = '$mota', idcty = $idcty $setHinh
                where idsp = $idsp";

        return $conn->query($sql);
    }

    public function xoaSanPham($idsp)
    {
        $conn = $this->connect();
        $idsp = intval($idsp);
        if ($idsp <= 0) {
            return false;
        }

        $sql = "delete from sanpham where idsp = $idsp";
        return $conn->query($sql);
    }

    public function danhsachsanpham($baseLink = "quanlysanpham.php")
    {
        $conn = $this->connect();
        $sql = "select sp.idsp, sp.ten, sp.gia, sp.giamgia, sp.mota, cty.tencty
                from sanpham sp
                left join congty cty on sp.idcty = cty.idcty
                order by sp.idsp asc";
        $kq = $conn->query($sql);

        echo '<table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:12px;">';
        echo '<tr style="background:#efefef; text-align:center;">';
        echo '<th style="width:60px;">STT</th>';
        echo '<th style="width:140px;">CONG TY</th>';
        echo '<th>TEN SP</th>';
        echo '<th style="width:120px;">GIA</th>';
        echo '<th style="width:120px;">GIAM GIA</th>';
        echo '<th style="width:120px;">GIA BAN</th>';
        echo '<th>MO TA</th>';
        echo '</tr>';

        if ($kq && $kq->num_rows > 0) {
            $stt = 1;
            while ($row = $kq->fetch_assoc()) {
                $idsp = intval($row['idsp']);
                echo '<tr>';
                echo '<td style="text-align:center;">' . $stt . '</td>';
                echo '<td>' . htmlspecialchars($row['tencty']) . '</td>';
                echo '<td><a href="' . htmlspecialchars($baseLink) . '?idsp=' . $idsp . '">' . htmlspecialchars($row['ten']) . '</a></td>';
                echo '<td style="text-align:right;">' . number_format($row['gia'], 0, ',', '.') . '</td>';
                $phanTram = 0;
                if (intval($row['gia']) > 0) {
                    $phanTram = round((intval($row['giamgia']) * 100) / intval($row['gia']), 2);
                }
                echo '<td style="text-align:right;">' . number_format($phanTram, 2, ',', '.') . '%</td>';
                echo '<td style="text-align:right; color:#c00;">' . number_format(max(0, intval($row['gia']) - intval($row['giamgia'])), 0, ',', '.') . '</td>';
                echo '<td>' . htmlspecialchars($row['mota']) . '</td>';
                echo '</tr>';
                $stt++;
            }
        } else {
            echo '<tr><td colspan="7" style="text-align:center; color:#c00;">Chua co san pham nao trong CSDL</td></tr>';
        }

        echo '</table>';
    }
}
?>