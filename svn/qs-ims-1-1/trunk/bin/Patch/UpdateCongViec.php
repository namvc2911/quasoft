<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (63, 'OKeHoachBaoTri', 'M724', 'OChuKyBaoTri', 'ChuKy', '0', 'ChuKy', 'Chu kỳ', 1, '', 10, 1, 0, 0, 4, 220, 0, '', '', 0, 0, '', '', '', '', '', 0, 1, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (50, 'OKeHoachBaoTri', 'M708', 'OPhanLoaiBaoTri', 'Loai', '0', 'LoaiBaoTri', 'Loại BT', 1, '', 10, 1, 0, 0, 4, 220, 1, '', '', 3, 1, '', '', '', '', 'M.Type', 1, 1, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (55, 'OKeHoachBaoTri', 'M724', 'OBaoTriDinhKy', 'MoTa', '0', 'MoTa', 'Tên công việc', 2, '', 10, 1, NULL, 0, 1, 200, 0, '', '', 3, 1, '', '', '', '', 'Description', 1, 2, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (11, 'OKeHoachBaoTri', 'M720', 'OKhuVuc', 'MaKhuVuc', 'Ten', 'MaKhuVuc', 'Mã khu vực', 1, '', 10, 1, NULL, 0, 4, 200, 0, '', '', 3, 0, '', '', '', '', 'Loc code', 1, 0, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (18, 'OKeHoachBaoTri', 'M705', 'OCauTrucThietBi', 'ViTri', 'BoPhan', 'BoPhan', 'Bộ phận', 1, '', 10, 1, 0, 0, 4, 220, 0, '', '', 3, 0, '', '', '', '', 'Component', 1, 1, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (80, 'OPhieuBaoTri', 'M724', 'OChuKyBaoTri', 'ChuKy', '0', 'ChuKy', 'Chu kỳ', 1, '', 10, 1, NULL, 0, 4, 220, 0, '', '', 0, 0, '', '', '', '', '', 0, 0, NULL);");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (55, 'OPhieuBaoTri', 'M708', 'OPhanLoaiBaoTri', 'Loai', '0', 'LoaiBaoTri', 'Loại BT', 1, '', 8, 1, NULL, 0, 4, 220, 1, '', '', 3, 1, '', '', '', '', 'M.Type', 1, 0, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (60, 'OPhieuBaoTri', 'M724', 'OBaoTriDinhKy', 'MoTa', '0', 'MoTa', 'Tên công việc', 2, '', 10, 1, 0, 0, 1, 205, 0, '', '', 3, 1, '', '', '', '', 'Task', 1, 0, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (50, 'OPhieuBaoTri', 'M705', 'OCauTrucThietBi', 'ViTri', 'BoPhan', 'BoPhan', 'Bộ phận', 1, '', 6, 1, NULL, 0, 4, 220, 0, '', '', 3, 0, '', '', '', '', 'Component', 1, 0, '');");


$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (35, 'OBaoTriDinhKy', '0', '0', '0', '0', 'MoTa', 'Tên công việc', 2, '', 10, 1, NULL, 0, 1, 200, 0, '', '', 3, 1, '', '', '', '', 'Description', 0, 2, '');");


$db->execute("update OBaoTriDinhKy set MoTa = LoaiBaoTri  where ifnull(MoTa,'') = '';");

$db->execute("delete from qsfields where ObjectCode = 'OBaoTriDinhKy' and FieldCode = 'LoaiBaoTri';");

$db->execute("delete from qsfields where ObjectCode = 'OPhanLoaiBaoTri' and FieldCode = 'DungTaoPhieu';");

$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (164, 'MaKhuVuc', 'OKeHoachBaoTri', 3, 0);");

$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (166, 'MaKhuVuc', 'OKeHoachBaoTri', 1, 0);");


if($object->v_fInit('OBaoTriDinhKy'))
{
    $object->createView();
}

if($object->v_fInit('OPhieuBaoTri'))
{
    $object->createView();
}

if($object->v_fInit('OKeHoachBaoTri'))
{
    $object->createView();
}

// 2 cau query update lai ref mo ta dinh ky