<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();

$db->execute("DELETE FROM qsfields WHERE ObjectCode = 'OPhieuBaoTri' AND FieldCode = 'LanBaoTri'");
$db->execute("DELETE FROM qsfields WHERE ObjectCode = 'OKeHoachBaoTri' AND FieldCode = 'LanBaoTri'");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (140, 'OPhieuBaoTri', 'M837', 'OKeHoachBaoTri', 'NgayBatDau', '0', 'NgayKeHoach', 'Ngày kế hoạch', 10, '', 10, 1, NULL, 0, 3, 100, 0, '', '', 0, 0, '', '', '', '', 'Plan date', 0, 0, '');");

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (130, 'OPhieuBaoTri', 'M838', 'OKeHoachTongThe', 'Ma', 'Ten', 'SoKeHoach', 'Kế hoạch tổng thể', 1, '', 10, 1, NULL, 0, 4, 200, 0, '', 'NgayKeHoach', 0, 0, '', '', '', '', 'General plan', 1, 0, '');");

$db->execute("REPLACE INTO `qsuiboxfields` (`UIBID`, `FieldCode`) VALUES (12, 'NgayKeHoach');");

$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (98, 'NgayKeHoach', 'OPhieuBaoTri', 3, 0);");
$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (99, 'NgayKeHoach', 'OPhieuBaoTri', 1, 0);");
$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (101, 'NgayKeHoach', 'OPhieuBaoTri', 1, 0);");
$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (141, 'NgayKeHoach', 'OPhieuBaoTri', 1, 0);");
$db->execute("REPLACE INTO `qssteprights` (`SID`, `FieldCode`, `ObjectCode`, `Rights`, `GroupID`) VALUES (142, 'NgayKeHoach', 'OPhieuBaoTri', 1, 0);");

if($object->v_fInit('OPhieuBaoTri'))
{
    $object->createView();
}

if($object->v_fInit('OKeHoachBaoTri'))
{
    $object->createView();
}