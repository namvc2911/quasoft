<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();

$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (90, 'OBaoTriDinhKy', '0', '0', '0', '0', 'MucDoUuTien', 'Mức độ ưu tiên', 5, '', 10, 1, NULL, 0, 5, 220, 0, '', '', 3, 1, '', '', '', '{\"0\":\"Bình thường (Normal)\",\"1\": \"Khẩn cấp (Urgent)\",\"2\": \"Nhanh (Fast)\"}', 'Priority', 0, 0, '');");


$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (35, 'OKeHoachBaoTri', '0', '', '0', '0', 'MucDoUuTien', 'Mức độ ưu tiên', 1, '', 10, 1, 0, 0, 5, 220, 1, '', '', 3, 1, '', '', '', '{\"0\":\"Bình thường (Normal)\",\"1\": \"Khẩn cấp (Urgent)\",\"2\": \"Nhanh (Fast)\"}', 'Priority', 0, 0, '');");


$db->execute("REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (80, 'OPhieuBaoTri', '0', '', '0', '0', 'MucDoUuTien', 'Mức độ ưu tiên', 5, '', 10, 1, NULL, 0, 3, 220, 1, '', '', 9, 1, '', '', '', '{\"0\":\"Bình thường (Normal)\",\"1\": \"Khẩn cấp (Urgent)\",\"2\": \"Nhanh (Fast)\"}', 'Priority', 0, 0, '');");

if($object->v_fInit('OBaoTriDinhKy'))
{
    $object->createView();
}

if($object->v_fInit('OKeHoachBaoTri'))
{
    $object->createView();
}

if($object->v_fInit('OPhieuBaoTri'))
{
    $object->createView();
}

$db->execute("
    UPDATE OBaoTriDinhKy 
    INNER JOIN OBaoTriDinhKy_Old ON OBaoTriDinhKy.IFID_M724 = OBaoTriDinhKy_Old.IFID_M724
    SET OBaoTriDinhKy.Ref_MucDoUuTien = 'Bình thường (Normal)', OBaoTriDinhKy.MucDoUuTien = 0 
    WHERE OBaoTriDinhKy_Old.MucDoUuTien = 'Bình thường (Normal)'");

$db->execute("
    UPDATE OBaoTriDinhKy 
    INNER JOIN OBaoTriDinhKy_Old ON OBaoTriDinhKy.IFID_M724 = OBaoTriDinhKy_Old.IFID_M724
    SET OBaoTriDinhKy.Ref_MucDoUuTien = 'Khẩn cấp (Urgent)', OBaoTriDinhKy.MucDoUuTien = 1 
    WHERE OBaoTriDinhKy_Old.MucDoUuTien = 'Khẩn cấp (Urgent)'");


$db->execute("
    UPDATE OBaoTriDinhKy 
    INNER JOIN OBaoTriDinhKy_Old ON OBaoTriDinhKy.IFID_M724 = OBaoTriDinhKy_Old.IFID_M724
    SET OBaoTriDinhKy.Ref_MucDoUuTien = 'Nhanh (Fast)', OBaoTriDinhKy.MucDoUuTien = 2
    WHERE OBaoTriDinhKy_Old.MucDoUuTien = 'Nhanh (Fast)'");

// -----

$db->execute("
    UPDATE OKeHoachBaoTri 
    INNER JOIN OKeHoachBaoTri_Old ON OKeHoachBaoTri.IFID_M837 = OKeHoachBaoTri_Old.IFID_M837
    SET OKeHoachBaoTri.Ref_MucDoUuTien = 'Bình thường (Normal)', OKeHoachBaoTri.MucDoUuTien = 0 
    WHERE OKeHoachBaoTri_Old.MucDoUuTien = 'Bình thường (Normal)'");

$db->execute("
    UPDATE OKeHoachBaoTri 
    INNER JOIN OKeHoachBaoTri_Old ON OKeHoachBaoTri.IFID_M837 = OKeHoachBaoTri_Old.IFID_M837
    SET OKeHoachBaoTri.Ref_MucDoUuTien = 'Khẩn cấp (Urgent)', OKeHoachBaoTri.MucDoUuTien = 1 
    WHERE OKeHoachBaoTri_Old.MucDoUuTien = 'Khẩn cấp (Urgent)'");


$db->execute("
    UPDATE OKeHoachBaoTri 
    INNER JOIN OKeHoachBaoTri_Old ON OKeHoachBaoTri.IFID_M837 = OKeHoachBaoTri_Old.IFID_M837
    SET OKeHoachBaoTri.Ref_MucDoUuTien = 'Nhanh (Fast)', OKeHoachBaoTri.MucDoUuTien = 2
    WHERE OKeHoachBaoTri_Old.MucDoUuTien = 'Nhanh (Fast)'");


// -----

$db->execute("
    UPDATE OPhieuBaoTri 
    INNER JOIN OPhieuBaoTri_Old ON OPhieuBaoTri.IFID_M759 = OPhieuBaoTri_Old.IFID_M759
    SET OPhieuBaoTri.Ref_MucDoUuTien = 'Bình thường (Normal)', OPhieuBaoTri.MucDoUuTien = 0 
    WHERE OPhieuBaoTri_Old.MucDoUuTien = 'Bình thường (Normal)'");

$db->execute("
    UPDATE OPhieuBaoTri 
    INNER JOIN OPhieuBaoTri_Old ON OPhieuBaoTri.IFID_M759 = OPhieuBaoTri_Old.IFID_M759
    SET OPhieuBaoTri.Ref_MucDoUuTien = 'Khẩn cấp (Urgent)', OPhieuBaoTri.MucDoUuTien = 1 
    WHERE OPhieuBaoTri_Old.MucDoUuTien = 'Khẩn cấp (Urgent)'");


$db->execute("
    UPDATE OPhieuBaoTri 
    INNER JOIN OPhieuBaoTri_Old ON OPhieuBaoTri.IFID_M759 = OPhieuBaoTri_Old.IFID_M759
    SET OPhieuBaoTri.Ref_MucDoUuTien = 'Nhanh (Fast)', OPhieuBaoTri.MucDoUuTien = 2
    WHERE OPhieuBaoTri_Old.MucDoUuTien = 'Nhanh (Fast)'");



