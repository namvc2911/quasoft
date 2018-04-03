<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();
/*try {
    $db->execute(sprintf("update `qsfields` set `Unique` = 1 where ObjectCode = 'OPhanLoaiBaoTri' and FieldCode = 'LoaiBaoTri';"));
}
catch(Exception $e)
{
    echo 'Error 1';
}
try {
    $db->execute(sprintf("ALTER TABLE `qsrecordrights` ADD COLUMN `Rights` INT NOT NULL DEFAULT '0';"));
}
catch(Exception $e)
{
    echo 'Error 1';
}
try {
    $db->execute(sprintf("update qsrecordrights set Rights = 31;"));
}
catch(Exception $e)
{
    echo 'Error 1';
}*/

//xóa duplicate
try {
	$sql = sprintf("INSERT INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES
			(1, 'OChuKyBaoTri', '0', '0', '0', '0', 'ID', 'ID', 1, 'AUTO', 3, 1, NULL, 0, 1, 100, 0, '', '', 3, 1, '', '', '', '', 'ID', 0, 2, '')");
	$db->execute($sql);
	$sql = sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_OChuKyBaoTri(IFID_M724 int,IOID int) select IFID_M724,IOID from OChuKyBaoTri');
	$db->execute($sql);
	$sql = sprintf('delete from OChuKyBaoTri where IOID not in (select max(IOID) from tmp_OChuKyBaoTri group by IFID_M724)');
	$db->execute($sql);
	$sql = sprintf('DROP TEMPORARY TABLE IF EXISTS tmp_IOID');
	$db->execute($sql);
	$object->v_fInit('OChuKyBaoTri');
	$object->createView();
	$sql = sprintf('update OChuKyBaoTri set ID = 1');
	$db->execute($sql);
	$sql = sprintf('update OChuKyBaoTri set ID = 1');
	$db->execute($sql);
}
catch(Exception $e)
{
   echo $e->getMessage();
}
