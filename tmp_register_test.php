<?php include 'classtmdt/clskhachhang.php'; $p=new khachhang(); $u='test_user_'.rand(1000,9999); $ok=$p->DangKyTaiKhoan($u,'123456','Test','User'); echo ($ok?'OK ':'FAIL ').$u;
