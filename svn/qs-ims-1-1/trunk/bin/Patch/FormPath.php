<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();

$sql = array();
$sql[] = "SET NAMES utf8;";

/*
 $sql[] = "delete from qsfields
			where ObjectCode = 'ODanhSachNhapKho'
			and (FieldCode='MaThietBi' or FieldCode='ViTri' or FieldCode='BoPhan' or FieldCode='DongDonHang')";



$sql[] = "delete from qsfields
			where ObjectCode = 'ODanhSachXuatKho' and (FieldCode='MaThietBi' or FieldCode='ViTri' or FieldCode='BoPhan' or FieldCode='DongDonHang');";
$sql[] = "REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`) VALUES (150, 'OXuatKho', 'M759', 'OPhieuBaoTri', 'SoPhieu', 'MaDVBT', 'PhieuBaoTri', 'Phiáº¿u báº£o trÃ¬', 1, '', NULL, 1, NULL, 0, 4, 100, 0, '', '', 1, 0, '', '', '', '', 'Workorder No', 0);";

$sql[] = "update
				 OPhieuBaoTri AS pbt 
				INNER JOIN OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759 
				set cv.Ngay = pbt.NgayBatDau
				WHERE cv.Ngay not between pbt.NgayBatDau and pbt.Ngay";

$sql[] = "update OCongViecBT set NhanCong = ifnull(NhanCong,1),ThoiGian = ifnull(ThoiGian,60)";
$sql[] = "update OCongViecBTPBT set NhanCong = ifnull(NhanCong,1),NhanCongDuKien = ifnull(NhanCongDuKien,1)
			,ThoiGian = ifnull(ThoiGian,60),ThoiGianDuKien = ifnull(ThoiGianDuKien,60)";
*/
/*$sql[] = "delete from qsfields where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'SoGio';";
$sql[] = "delete from qsfields where ObjectCode = 'ODinhMucThietBi' and FieldCode = 'GioChayMay';";
$sql[] = "delete from qsfields where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'Gio';";
$sql[] = "delete from qsfields where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'Ky';";
$sql[] = "delete from qsfields where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'GioBD';";
$sql[] = "delete from qsfields where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'GioKT';";
$sql[] = "INSERT IGNORE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`) VALUES
									(50, 'ONhatTrinhThietBi', 'M701', 'OCa', 'MaCa', 'TenCa', 'Ca', 'Ca', 1, '', NULL, 1, NULL, 0, 3, 100, 0, '', '', 1, 0, '', '', '', '', 'Shift', 0),
									(60, 'ONhatTrinhThietBi', 'M316', 'ODanhSachNhanVien', 'TenNhanVien', 'MaNhanVien', 'NguoiVanHanh', 'NgÆ°á»i váº­n hÃ nh', 1, '', NULL, 1, NULL, 0, 4, 200, 0, '', '', 1, 0, '', '', '', '', 'Operator', 0);";
$sql[] = "update qsfields set DefaultVal = 'UNIQUE' where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'MaTB';";
$sql[] = "update qsfields set DefaultVal = 'UNIQUE' where ObjectCode = 'ONhatTrinhThietBi' and FieldCode = 'Ngay';";
//Vật tư thay thế có serial và ngày
$sql[] = "INSERT IGNORE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`) VALUES
									(6, 'OVatTuPBT', '0', '0', '0', '0', 'Ngay', 'NgÃ y', 10, '', NULL, 1, NULL, 0, 8, 100, 0, '', '', 1, 0, '', '', '', '', 'Date', 0),
									(33, 'OVatTuPBT', 'M775', 'ODanhMucChiTietThietBi', 'So', 'Ten', 'SoChiTiet', 'Sá»‘ chi tiáº¿t', 1, '', NULL, 1, NULL, 0, 4, 100, 0, '', '', 1, 0, '', '', '', '', 'Serial', 0);";
$object->v_fInit('ODinhMucThietBi');
$object->createView();
$object->v_fInit('ONhatTrinhThietBi');
$object->createView();
$object->v_fInit('OVatTuPBT');
$object->createView();

$sql[] = "delete from qsfobjects where FormCode = 'M765' and ObjectCode = 'OThongKeSanLuong';";
$object->v_fInit('OThongKeSanLuong');
$object->createView();

$sql[] = "delete from qsfobjects where FormCode = 'M732' and ObjectCode = 'ODinhMucSanPham';";


$sql[] = "delete from qsfields where ObjectCode = 'OThongKeSanLuong' and FieldCode = 'SanLuong';";
$sql[] = "delete from qsfields where ObjectCode = 'OThongKeSanLuong' and FieldCode = 'SoLuongKeHoach';";
$sql[] = "delete from qsfields where ObjectCode = 'OThongKeSanLuong' and FieldCode = 'ThaoDo';";
$object->v_fInit('OThongKeSanLuong');
$object->createView();

$sql[] = "delete from qsfields where ObjectCode = 'OSanXuat' and FieldCode = 'SanXuatSuaChua';";
$object->v_fInit('OSanXuat');
$object->createView();
*/


$sql[] = "update qsforms set class='/static/m779' where FormCode = 'M779';";
$sql[] = "update qsforms set class='/static/m728' where FormCode = 'M728';";
$sql[] = "update qsforms set class='/static/m729' where FormCode = 'M729';";
$sql[] = "update qsforms set class='/static/m780' where FormCode = 'M780';";
$sql[] = "update qsforms set class='/static/m778' where FormCode = 'M778';";
$sql[] = "update qsforms set class='/static/m604' where FormCode = 'M604';";
$sql[] = "update qsforms set class='/static/m749' where FormCode = 'M749';";
$sql[] = "update qsforms set class='/static/m739' where FormCode = 'M739';";
$sql[] = "update qsforms set class='/static/m727' where FormCode = 'M727';";
$sql[] = "update qsforms set class='/static/m743' where FormCode = 'M743';";
$sql[] = "update qsforms set class='/static/m744' where FormCode = 'M744';";
$sql[] = "update qsforms set class='/static/m745' where FormCode = 'M745';";
$sql[] = "update qsforms set class='/static/m750' where FormCode = 'M750';";
$sql[] = "update qsforms set class='/static/m731' where FormCode = 'M731';";
$sql[] = "update qsforms set class='/static/m738' where FormCode = 'M738';";
$sql[] = "update qsforms set class='/static/m757' where FormCode = 'M757';";
$sql[] = "update qsforms set class='/static/m726' where FormCode = 'M726';";
$sql[] = "update qsforms set class='/static/m734' where FormCode = 'M734';";
$sql[] = "update qsforms set class='/static/m756' where FormCode = 'M756';";
$sql[] = "update qsforms set class='/static/m620' where FormCode = 'M620';";
$sql[] = "update qsforms set class='/static/m794' where FormCode = 'M794';";
$sql[] = "update qsforms set class='/static/m798' where FormCode = 'M798';";
$sql[] = "update qsforms set class='/static/m748' where FormCode = 'M748';";
$sql[] = "update qsforms set class='/static/m810' where FormCode = 'M810';";
$sql[] = "update qsforms set class='/static/m811' where FormCode = 'M811';";
$sql[] = "update qsforms set class='/static/m812' where FormCode = 'M812';";
$sql[] = "update qsforms set class='/static/m736' where FormCode = 'M736';";
$sql[] = "update qsforms set class='/static/m772' where FormCode = 'M772';";
$sql[] = "update qsforms set class='/static/m777' where FormCode = 'M777';";
$sql[] = "update qsforms set class='/static/m793' where FormCode = 'M793';";
$sql[] = "update qsforms set class='/static/m792' where FormCode = 'M792';";
$sql[] = "update qsforms set class='/static/m730' where FormCode = 'M730';";
$sql[] = "update qsforms set class='/static/m786' where FormCode = 'M786';";
$sql[] = "update qsforms set class='/static/m789' where FormCode = 'M789';";
$sql[] = "update qsforms set class='/static/m741' where FormCode = 'M741';";
$sql[] = "update qsforms set class='/static/m742' where FormCode = 'M742';";
$sql[] = "update qsforms set class='/static/m768' where FormCode = 'M768';";
$sql[] = "update qsforms set class='/static/m755' where FormCode = 'M755';";
$sql[] = "update qsforms set class='/static/m791' where FormCode = 'M791';";
$sql[] = "update qsforms set class='/static/m782' where FormCode = 'M782';";
$sql[] = "update qsforms set class='/static/m781' where FormCode = 'M781';";
$sql[] = "update qsforms set class='/static/m785' where FormCode = 'M785';";
$sql[] = "update qsforms set class='/static/m783' where FormCode = 'M783';";
$sql[] = "update qsforms set class='/static/m758' where FormCode = 'M758';";


/* ADD: 2016-01-14 */
$sql[] = "update qsforms set class='/static/m608' where FormCode = 'M618';"; // Bao cao the kho
$sql[] = "update qsforms set class='/static/m608' where FormCode = 'M608';"; // Bieu do ton kho
$sql[] = "update qsforms set class='/static/m609' where FormCode = 'M609';"; // Bieu do ton kho theo kỳ
$sql[] = "update qsforms set class='/static/m619' where FormCode = 'M619';"; // Báo cáo nhập kho theo nhà cung cấp
$sql[] = "update qsforms set class='/static/m621' where FormCode = 'M621';"; // Báo cáo xuất nhập tồn theo số lượng
$sql[] = "update qsforms set class='/static/m784' where FormCode = 'M784';"; // Tồn kho theo vị tri
$sql[] = "update qsforms set class='/static/m760' where FormCode = 'M760';"; // Xuất nhập tồn theo giá trị
$sql[] = "update qsforms set class='/static/m790' where FormCode = 'M790';"; // Đối chiếu phụ tùng vật tư
$sql[] = "update qsforms set class='/static/m752' where FormCode = 'M752';"; // Báo cáo xuất nhập tồn theo loại
$sql[] = "update qsforms set class='/static/m622' where FormCode = 'M622';"; // Tổng hợp nhập xuất tồn tại đơn vị sản xuất
$sql[] = "update qsforms set class='/static/m625' where FormCode = 'M625';"; // Tổng hợp nhập xuất tồn tại đơn vị sản xuất

$sql[] = "update qsforms set class='/static/m006' where FormCode = 'M006';";
$sql[] = "update qsforms set class='/static/m005' where FormCode = 'M005';";

$sql[] = "CREATE TABLE qsrecordrights (
			`GroupID` INT NOT NULL ,
			`FormCode` VARCHAR( 50 ) NOT NULL ,
			`IFID` INT NOT NULL ,
			PRIMARY KEY  (
			`GroupID` ,
			`FormCode` ,
			`IFID`
			)
		) ENGINE = InnoDB;";

$sql[] = "ALTER TABLE `qsforms` ADD `Secure` TINYINT NOT NULL ;";

$sql[] = "update qsforms set Secure = 1 where FormCode = 'M601' or FormCode = 'M720' or FormCode = 'M125';";

$sql[] = "update qsforms set Secure = 1 where FormCode = 'M601' or  FormCode = 'M720';";

$sql[] = "ALTER TABLE `qsstepapprover` DROP `GroupID` ;";

$sql[] = "ALTER TABLE `qsstepapprover` DROP PRIMARY KEY ;";

$sql[] = "ALTER TABLE `qsstepapprover` ADD `SAID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ,
			ADD `Name` VARCHAR( 255 ) NOT NULL AFTER `SAID`;";

$sql[] = "CREATE TABLE qsstepapproverrights (
				`SAID` INT NOT NULL ,
				`UID` INT NOT NULL ,
				PRIMARY KEY ( `SAID` , `UID` )
			) ENGINE = InnoDB;";

$sql[] = "ALTER TABLE `qsfapprover` CHANGE `GroupID` `SAID` INT( 11 ) NOT NULL";
$sql[] = "ALTER TABLE `qsrecordrights` CHANGE `GroupID` `UID` INT( 11 ) NOT NULL;";




foreach($sql as $item)
{
	$db->execute($item);
}
?>