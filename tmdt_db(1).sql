-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 26, 2026 at 03:01 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tmdt_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `congty`
--

CREATE TABLE `congty` (
  `idcty` int(11) NOT NULL,
  `tencty` varchar(100) NOT NULL,
  `diachi` varchar(255) default NULL,
  `dienthoai` varchar(20) default NULL,
  `fax` varchar(20) default NULL,
  PRIMARY KEY  (`idcty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `congty`
--

INSERT INTO `congty` (`idcty`, `tencty`, `diachi`, `dienthoai`, `fax`) VALUES
(1, 'Iphone', '136 Ấp Bình Hòa, Đức Tân , Tân Trụ , Long An', '0849842115', '024356789'),
(2, 'Samsung ', '300 Nguyễn Thái Sơn, Gò Vấp', '0946390318', '0944693446'),
(3, 'Vivo', '500 Phan Văn Trị , Gò Vấp', '0911897419', '0946499700');

-- --------------------------------------------------------

--
-- Table structure for table `dathang`
--

CREATE TABLE `dathang` (
  `iddh` int(10) NOT NULL auto_increment,
  `idkh` varchar(10) default NULL,
  `id_nhanvien` varchar(10) default NULL,
  `ngaydatdathang` datetime default NULL,
  `thietlaplayngaygiaomacdinh` date default NULL,
  `trangthai` varchar(20) default 'chuaxacnhan',
  PRIMARY KEY  (`iddh`),
  KEY `idkh` (`idkh`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `dathang`
--

INSERT INTO `dathang` (`iddh`, `idkh`, `id_nhanvien`, `ngaydatdathang`, `thietlaplayngaygiaomacdinh`, `trangthai`) VALUES
(19, '0', NULL, '2026-10-26 01:19:55', NULL, 'chuaxacnhan'),
(20, '4', NULL, '2026-10-26 13:45:34', NULL, 'danggiao'),
(21, '0', NULL, '2026-10-26 13:49:35', NULL, 'chuaxacnhan'),
(22, '4', NULL, '2026-10-26 14:11:00', NULL, 'danggiao'),
(23, '4', NULL, '2026-10-26 14:56:46', NULL, 'dagiao'),
(24, '5', NULL, '2026-10-26 15:00:31', NULL, 'chuaxacnhan');

-- --------------------------------------------------------

--
-- Table structure for table `dathang_chitiet`
--

CREATE TABLE `dathang_chitiet` (
  `iddh` varchar(10) NOT NULL,
  `idsp` varchar(10) NOT NULL,
  `soluong` int(11) NOT NULL,
  `dongia` decimal(10,2) NOT NULL,
  `giamgia` decimal(10,2) default '0.00',
  PRIMARY KEY  (`iddh`,`idsp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dathang_chitiet`
--

INSERT INTO `dathang_chitiet` (`iddh`, `idsp`, `soluong`, `dongia`, `giamgia`) VALUES
('1', '2', 3, '1000.00', '0.00'),
('1', '5', 4, '200.00', '0.00'),
('10', '2', 1, '1000.00', '0.00'),
('11', '2', 3, '1000.00', '0.00'),
('12', '2', 1, '1000.00', '0.00'),
('19', '18', 1, '20000000.00', '2000001.00'),
('2', '5', 1, '200.00', '0.00'),
('20', '5', 1, '8000000.00', '800000.00'),
('21', '19', 5, '30000000.00', '2000001.00'),
('22', '6', 1, '5600000.00', '560000.00'),
('23', '18', 1, '20000000.00', '2000000.00'),
('24', '19', 15, '30000000.00', '2001000.00'),
('3', '6', 2, '1200.00', '0.00'),
('4', '4', 2, '600.00', '0.00'),
('5', '4', 1, '600.00', '0.00'),
('6', '4', 1, '600.00', '0.00'),
('7', '5', 2, '200.00', '0.00'),
('8', '5', 2, '200.00', '0.00'),
('9', '2', 1, '1000.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `khachhang`
--

CREATE TABLE `khachhang` (
  `idkh` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `hodem` varchar(50) default NULL,
  `ten` varchar(50) default NULL,
  `diachi` varchar(100) default NULL,
  `diachinhanhang` varchar(100) default NULL,
  `dienthoai` varchar(15) default NULL,
  PRIMARY KEY  (`idkh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `khachhang`
--

INSERT INTO `khachhang` (`idkh`, `email`, `password`, `hodem`, `ten`, `diachi`, `diachinhanhang`, `dienthoai`) VALUES
('0', '', '', 'admin', 'admin', NULL, '300 nguyen thai sơn', NULL),
('4', '', '', NULL, 'KhachHang4', NULL, '300 nguyen thai sơn', NULL),
('5', '', '', 'bao', 'tran', NULL, 'bao tran cute', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

CREATE TABLE `sanpham` (
  `idsp` int(11) NOT NULL auto_increment,
  `ten` varchar(255) default NULL,
  `gia` int(11) default NULL,
  `mota` text,
  `idcty` int(11) default NULL,
  `giamgia` int(11) default '0',
  `hinh` varchar(255) default NULL,
  PRIMARY KEY  (`idsp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `sanpham`
--

INSERT INTO `sanpham` (`idsp`, `ten`, `gia`, `mota`, `idcty`, `giamgia`, `hinh`) VALUES
(4, 'Samsung Galaxy A06', 6000000, 'Samsung A06 phu hop cho hs sv', 2, 600000, 'samsunga06.png'),
(5, 'Samsung Galaxy S8', 8000000, 'Samsung S8 cung on', 2, 800000, 'samsungs8.png'),
(6, 'Vivo Y56', 5600000, 'Vivo Y56 chup hinh kha dep nen mua', 3, 560000, 'Vivo Y56.png'),
(18, 'Iphone15_Promax', 20000000, 'Iphone 15 gia hoi mac chac hong mua noi', 1, 2000000, 'Iphone 15.png'),
(19, 'Iphone17_Promax', 30000000, 'Ịphone 17 prm danh cho ng co tien', 1, 2001000, 'iphone17_Promax.png');

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan`
--

CREATE TABLE `taikhoan` (
  `iduser` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hodem` varchar(50) default NULL,
  `ten` varchar(50) NOT NULL,
  `phanquyen` varchar(20) NOT NULL,
  `landangnhapcuoi` datetime default NULL,
  PRIMARY KEY  (`iduser`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `taikhoan`
--

INSERT INTO `taikhoan` (`iduser`, `username`, `password`, `hodem`, `ten`, `phanquyen`, `landangnhapcuoi`) VALUES
(0, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'Anh', 'Trọng Phúc', 'admin', '2026-10-26 15:00:47'),
(4, 'trongphuc', 'e10adc3949ba59abbe56e057f20f883e', 'Lê Trọng', 'Phúc', 'user', '2026-10-26 14:56:35'),
(5, 'baotran', 'e10adc3949ba59abbe56e057f20f883e', 'bao', 'tran', 'user', '2026-10-26 14:59:46');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dathang`
--
ALTER TABLE `dathang`
  ADD CONSTRAINT `dathang_ibfk_1` FOREIGN KEY (`idkh`) REFERENCES `khachhang` (`idkh`);
