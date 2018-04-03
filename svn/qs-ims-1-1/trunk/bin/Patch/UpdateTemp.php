<?php

include "../sysbase.php";
$db    = Qss_Db::getAdapter('main');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$arrDB = array('anphatplastic',
'benhvienk',
'basic',
'basic1',
'cadivi',
'cmms',
'danangport',
'dqm',
'eccbk',
'hfh',
'hoaphat',
'internal',
'mts',
'namuga',
'nichirin',
'pm3',
'pos',
'pos_3',
'sabeco',
'sdm',
'shiseido',
'shiv',
'standard',
'vci',
'vietduc',
'vietduc_old',
'vit',
'vita',
'viwasupco',
'x48',
'x50');

/*foreach($arrDB as $dbname)
{
	try 
	{
		$db->execute(sprintf('update %1$s.qsstepgroups set Rights = if((Rights&16),Rights|32,Rights)',$dbname));
	}
	catch(Exception $e)
	{
	    echo "Error in " . $dbname . "\n";
	}
}
foreach($arrDB as $dbname)
{
	try 
	{
		$db->execute(sprintf('INSERT INTO %1$s.`qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`, `Style`) VALUES (45, "ODanhSachDiemDo", "0", "0", "0", "0", "SoHoatDongNgay", "Giá trị trung bình ngày", 6, "", 0, 1, NULL, 0, 1, 50, 0, "2", "", 3, 0, "", "","", "", "Daily value", 0, 0, "")',$dbname));
		$db->execute(sprintf('ALTER TABLE %1$s.`ODanhSachDiemDo` ADD COLUMN `SoHoatDongNgay` DECIMAL(24,2) NULL DEFAULT NULL',$dbname));
		$db->execute(sprintf('update %1$s.ODanhSachDiemDo as ODanhSachDiemDo 
						inner join %1$s.ODanhSachThietBi as ODanhSachThietBi on ODanhSachThietBi.IFID_M705 = ODanhSachDiemDo.IFID_M705
						left join %1$s.OLichLamViec as OLichLamViec on OLichLamViec.IOID = ODanhSachThietBi.Ref_LichLamViec
						set ODanhSachDiemDo.SoHoatDongNgay = OLichLamViec.SoHoatDongNgay'
			,$dbname));
	}
	catch(Exception $e)
	{
	    echo "Error in " . $dbname . "\n";
	}
}
foreach($arrDB as $dbname)
{
	try 
	{
		$db->execute(sprintf('ALTER TABLE %1$s.`qsworkflowsteps` ADD COLUMN `QuickStep` VARCHAR(50) NULL DEFAULT NULL;',$dbname));
	}
	catch(Exception $e)
	{
	    echo "Error in " . $dbname . "\n";
	}
}
*/
/*foreach($arrDB as $dbname)
{
	try 
	{
		$db->execute(sprintf('CREATE TABLE %1$s.`qsfvalidategroups` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`GroupID` INT NOT NULL ,
UNIQUE (
`FormCode` ,
`GroupID`
)
) ENGINE = InnoDB;',$dbname));
	}
	catch(Exception $e)
	{
	    echo "Error in " . $dbname . "\n";
	}
}
*/
foreach($arrDB as $dbname)
{
	try 
	{
		//$db->execute(sprintf('ALTER TABLE %1$s.`qsfobjects` ADD COLUMN `RefFormCode` VARCHAR(40);',$dbname));
		//$db->execute("INSERT INTO {$dbname}.`qsbash` (`FormCode`, `ToFormCode`, `ObjectCode`, `BID`, `BashName`, `Type`, `UID`, `CDate`, `Step`, `Active`, `Record`, `Class`, `BashName_en`) VALUES ('M837', 'M724', NULL, 96, 'Tạo từ kế hoạch định kỳ', 4, -1, '2017-12-29 14:49:15', '1,2', 1, 0, '/static/m838/detail/plan', 'Create from periodic');");
		
	}
	catch(Exception $e)
	{
	    echo "Error in " . $dbname . "\n";
	}
}