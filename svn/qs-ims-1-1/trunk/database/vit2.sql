-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               10.1.19-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table standard.qsmenu
DROP TABLE IF EXISTS `qsmenu`;
CREATE TABLE IF NOT EXISTS `qsmenu` (
  `MenuID` int(11) NOT NULL AUTO_INCREMENT,
  `MID` int(11) DEFAULT NULL,
  `MenuName` varchar(255) NOT NULL,
  `ParentID` int(11) NOT NULL,
  `MenuOrder` smallint(6) NOT NULL DEFAULT '0',
  `MenuName_en` varchar(255) DEFAULT NULL,
  `Icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`MenuID`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8;

-- Dumping data for table standard.qsmenu: ~55 rows (approximately)
/*!40000 ALTER TABLE `qsmenu` DISABLE KEYS */;
INSERT INTO `qsmenu` (`MenuID`, `MID`, `MenuName`, `ParentID`, `MenuOrder`, `MenuName_en`, `Icon`) VALUES
	(90, 3, 'Import', 0, 170, 'Import', 'import'),
	(94, 3, 'Kho', 0, 140, 'Stock', 'inventory'),
	(97, 3, 'Phiếu bảo trì', 0, 90, 'Work order', 'workorder'),
	(98, 3, 'Bảo trì', 122, 50, 'Mainternance setting', ''),
	(100, 3, 'Kế hoạch', 0, 80, 'Planning', 'calendar'),
	(106, 3, 'Giám sát', 0, 70, 'Monitor', 'monitoring'),
	(107, 3, 'Hệ thống', 0, 180, 'System', 'system'),
	(109, 3, 'Sản xuất', 0, 130, 'Production', 'production'),
	(114, 3, 'Thiết bị', 0, 30, 'Equipments', 'equipment'),
	(117, 3, 'Lịch làm việc', 122, 60, 'Calendar setup', ''),
	(118, 3, 'Mua hàng', 0, 150, 'Purchase', 'purchase'),
	(122, 3, 'Cài đặt', 0, 10, 'General setting', 'config'),
	(124, 2, 'Hệ thống', 0, 10, 'Administration', NULL),
	(125, 2, 'Cá nhân', 124, 10, 'Personal', NULL),
	(126, 2, 'Quản trị hệ thống', 124, 20, 'Administration', NULL),
	(127, 2, 'Quản lý tiến trình', 124, 30, 'Process management', NULL),
	(128, 2, 'Cài đặt chung', 0, 20, 'General setup', NULL),
	(129, 2, 'Danh mục', 128, 10, 'List', NULL),
	(130, 2, 'Lịch làm việc', 128, 20, 'Working calendar', NULL),
	(131, 2, 'Sản xuất', 128, 30, 'Production', NULL),
	(132, 2, 'Kho', 0, 30, 'Stock', NULL),
	(133, 2, 'Mua hàng', 0, 40, 'Purchase', NULL),
	(134, 2, 'Sản xuất', 0, 50, 'Production', NULL),
	(135, 2, 'Giao dịch', 132, 10, 'Transaction', NULL),
	(136, 2, 'Báo cáo', 132, 20, 'Reports', NULL),
	(137, 2, 'Cài đặt', 132, 30, 'Setup', NULL),
	(138, 2, 'Giao dịch', 133, 10, 'Transaction', NULL),
	(139, 2, 'Xử lý', 133, 20, 'Processing', NULL),
	(140, 2, 'Nhận hàng', 133, 30, 'Reciept', NULL),
	(141, 2, 'Kế hoạch', 134, 10, 'Plan', NULL),
	(142, 2, 'Giao dịch', 134, 20, 'Transaction', NULL),
	(143, 2, 'Báo cáo', 134, 30, 'Reports', NULL),
	(145, 3, 'Nhắc việc', 0, 20, 'Alert', 'alert'),
	(147, 3, 'Yêu cầu', 0, 60, 'Service request', 'request'),
	(150, 3, 'Tài sản', 0, 120, 'Accounting', 'accounting'),
	(152, 3, 'HC/KĐ', 0, 100, 'HC/KĐ', 'calibrate'),
	(155, 3, 'Cài đặt chung', 122, 10, 'General', 'config'),
	(156, 3, 'Nhân sự', 122, 20, 'Employees', 'hrm'),
	(157, 3, 'Kho', 122, 30, 'Stock', 'inventory'),
	(158, 3, 'Thiết bị', 122, 40, 'Equipments', 'equipment'),
	(159, 3, 'Cảnh báo', 122, 70, 'Alert', ''),
	(160, 3, 'Sản xuất', 122, 80, 'Production', ''),
	(161, 3, 'Hoạt động', 0, 110, 'Operation', 'service'),
	(162, 3, 'Báo cáo', 0, 160, 'Reports', 'analyst'),
	(163, 3, 'Thiết bị', 162, 10, 'Equipments', ''),
	(164, 3, 'Nhân sự', 162, 20, 'Employees', ''),
	(165, 3, 'Bảo trì', 162, 30, 'Maintain', ''),
	(166, 3, 'Tài sản', 162, 40, 'Assets', ''),
	(167, 3, 'Kho', 162, 50, 'Stock', ''),
	(168, 3, 'Vật tư bảo trì', 162, 50, 'Materials', ''),
	(169, 3, 'Mua hàng', 162, 60, 'Purchase', ''),
	(170, 3, 'Sản xuất', 162, 80, 'Production', ''),
	(171, 3, 'Chi phí', 162, 90, 'Cost', ''),
	(172, 3, 'Dừng máy/ sự cố', 162, 100, 'Breakdown', ''),
	(173, 3, 'Hiệu suất', 162, 110, 'Performance', '');
/*!40000 ALTER TABLE `qsmenu` ENABLE KEYS */;


-- Dumping structure for table standard.qsmenulink
DROP TABLE IF EXISTS `qsmenulink`;
CREATE TABLE IF NOT EXISTS `qsmenulink` (
  `MenuID` int(11) NOT NULL,
  `FormCode` varchar(40) NOT NULL,
  `MenuLinkOrder` smallint(6) DEFAULT '0',
  UNIQUE KEY `MenuID` (`MenuID`,`FormCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table standard.qsmenulink: ~202 rows (approximately)
/*!40000 ALTER TABLE `qsmenulink` DISABLE KEYS */;
INSERT INTO `qsmenulink` (`MenuID`, `FormCode`, `MenuLinkOrder`) VALUES
	(90, 'M003', 10),
	(94, 'M402', 20),
	(94, 'M506', 10),
	(94, 'M604', 50),
	(94, 'M612', 40),
	(94, 'M616', 30),
	(97, 'M161', 40),
	(97, 'M748', 20),
	(97, 'M759', 10),
	(97, 'M779', 30),
	(98, 'M708', 20),
	(98, 'M714', 30),
	(98, 'M724', 10),
	(98, 'M807', 40),
	(100, 'M837', 20),
	(100, 'M838', 10),
	(106, 'M728', 10),
	(106, 'M729', 20),
	(107, 'M004', 30),
	(107, 'M005', 10),
	(107, 'M006', 20),
	(109, 'M710', 20),
	(109, 'M717', 30),
	(109, 'M764', 50),
	(109, 'M783', 40),
	(112, 'M133', 10),
	(113, 'M734', 30),
	(114, 'M173', 20),
	(114, 'M188', 30),
	(114, 'M189', 40),
	(114, 'M780', 10),
	(116, 'M704', 30),
	(116, 'M770', 20),
	(117, 'M107', 30),
	(117, 'M110', 20),
	(117, 'M701', 10),
	(118, 'M401', 10),
	(118, 'M412', 5),
	(119, 'M406', 30),
	(119, 'M716', 20),
	(120, 'M220', 30),
	(120, 'M408', 10),
	(120, 'M410', 40),
	(121, 'M106', 50),
	(121, 'M407', 40),
	(125, 'M003', 10),
	(125, 'M012', 20),
	(126, 'M005', 10),
	(126, 'M006', 20),
	(127, 'M019', 10),
	(127, 'M023', 20),
	(127, 'M024', 30),
	(127, 'M124', 40),
	(128, 'M113', 10),
	(128, 'M118', 20),
	(129, 'M102', 20),
	(129, 'M106', 60),
	(129, 'M112', 10),
	(129, 'M146', 30),
	(130, 'M107', 30),
	(130, 'M110', 20),
	(130, 'M701', 10),
	(131, 'M114', 40),
	(131, 'M125', 10),
	(131, 'M316', 1),
	(131, 'M702', 30),
	(131, 'M703', 20),
	(134, 'M758', 20),
	(134, 'M783', 10),
	(135, 'M402', 10),
	(135, 'M506', 20),
	(135, 'M604', 100),
	(135, 'M612', 30),
	(135, 'M616', 50),
	(136, 'M602', 20),
	(136, 'M607', 30),
	(136, 'M609', 40),
	(136, 'M618', 10),
	(136, 'M619', 50),
	(136, 'M621', 60),
	(136, 'M752', 80),
	(136, 'M760', 70),
	(136, 'M784', 90),
	(137, 'M601', 20),
	(137, 'M613', 10),
	(138, 'M401', 40),
	(138, 'M406', 30),
	(138, 'M407', 50),
	(138, 'M412', 10),
	(138, 'M716', 20),
	(139, 'M405', 10),
	(139, 'M411', 20),
	(139, 'M413', 30),
	(139, 'M415', 40),
	(140, 'M220', 30),
	(140, 'M404', 20),
	(140, 'M408', 10),
	(140, 'M410', 40),
	(141, 'M764', 10),
	(141, 'M901', 20),
	(142, 'M710', 10),
	(142, 'M712', 20),
	(142, 'M717', 30),
	(143, 'M762', 10),
	(143, 'M763', 20),
	(143, 'M766', 30),
	(144, 'M405', 10),
	(144, 'M411', 20),
	(144, 'M413', 30),
	(144, 'M415', 50),
	(145, 'M016', 100),
	(145, 'M174', 10),
	(147, 'M747', 10),
	(150, 'M151', 10),
	(150, 'M182', 20),
	(150, 'M183', 30),
	(152, 'M753', 10),
	(155, 'M001', 90),
	(155, 'M102', 30),
	(155, 'M106', 70),
	(155, 'M112', 20),
	(155, 'M113', 10),
	(155, 'M118', 40),
	(155, 'M126', 60),
	(155, 'M146', 50),
	(155, 'M186', 80),
	(156, 'M125', 20),
	(156, 'M316', 10),
	(156, 'M319', 30),
	(156, 'M343', 40),
	(156, 'M344', 50),
	(157, 'M601', 10),
	(157, 'M613', 20),
	(157, 'M614', 30),
	(158, 'M127', 60),
	(158, 'M172', 50),
	(158, 'M704', 40),
	(158, 'M705', 20),
	(158, 'M719', 70),
	(158, 'M720', 10),
	(158, 'M770', 30),
	(158, 'M844', 80),
	(159, 'M019', 10),
	(159, 'M023', 20),
	(159, 'M024', 30),
	(159, 'M723', 40),
	(160, 'M114', 30),
	(160, 'M702', 10),
	(160, 'M703', 20),
	(161, 'M707', 30),
	(161, 'M765', 20),
	(161, 'M787', 40),
	(161, 'M840', 10),
	(161, 'M849', 50),
	(163, 'M160', 50),
	(163, 'M418', 60),
	(163, 'M726', 10),
	(163, 'M738', 30),
	(163, 'M757', 20),
	(163, 'M778', 90),
	(163, 'M786', 70),
	(163, 'M794', 40),
	(163, 'M843', 80),
	(164, 'M176', 10),
	(164, 'M781', 20),
	(164, 'M782', 30),
	(164, 'M785', 40),
	(164, 'M835', 50),
	(165, 'M730', 50),
	(165, 'M731', 10),
	(165, 'M733', 60),
	(165, 'M736', 20),
	(165, 'M792', 30),
	(165, 'M793', 40),
	(166, 'M184', 20),
	(166, 'M185', 10),
	(166, 'M420', 30),
	(167, 'M602', 10),
	(167, 'M607', 20),
	(167, 'M608', 40),
	(167, 'M618', 30),
	(167, 'M619', 50),
	(167, 'M621', 60),
	(167, 'M752', 80),
	(167, 'M760', 70),
	(167, 'M784', 90),
	(168, 'M734', 10),
	(168, 'M756', 20),
	(168, 'M768', 40),
	(168, 'M790', 30),
	(169, 'M416', 10),
	(169, 'M417', 20),
	(171, 'M741', 10),
	(171, 'M742', 30),
	(171, 'M789', 20),
	(172, 'M715', 50),
	(172, 'M743', 10),
	(172, 'M744', 30),
	(172, 'M745', 40),
	(172, 'M750', 20),
	(173, 'M739', 10),
	(173, 'M773', 20);
/*!40000 ALTER TABLE `qsmenulink` ENABLE KEYS */;


-- Dumping structure for table standard.qsmenus
DROP TABLE IF EXISTS `qsmenus`;
CREATE TABLE IF NOT EXISTS `qsmenus` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(150) NOT NULL,
  `Default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Dumping data for table standard.qsmenus: ~2 rows (approximately)
/*!40000 ALTER TABLE `qsmenus` DISABLE KEYS */;
INSERT INTO `qsmenus` (`ID`, `Name`, `Default`) VALUES
	(2, 'Production', 0),
	(3, 'CMMS', 1);
/*!40000 ALTER TABLE `qsmenus` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
