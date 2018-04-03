ALTER TABLE `qsmenu` ADD `Icon` VARCHAR( 50 ) NULL;
ALTER TABLE `qssteprights` ADD `GroupID` INT NOT NULL DEFAULT '0';
ALTER TABLE `qssteprights` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `SID` , `FieldCode` , `ObjectCode` , `GroupID` ) ;

ALTER TABLE `qsforms` CHANGE `Adate` `ExcelImport` TINYINT NOT NULL DEFAULT '0';
update qsforms set ExcelImport = 0;
update qsforms set ExcelImport = 1 where FormCode = 'M705';
update qsforms set ExcelImport = 1 where FormCode = 'M724';
update qsforms set ExcelImport = 1 where FormCode = 'M102';
update qsforms set ExcelImport = 1 where FormCode = 'M113';
update qsforms set ExcelImport = 1 where FormCode = 'M118';
update qsforms set ExcelImport = 1 where FormCode = 'M770';
update qsforms set ExcelImport = 1 where FormCode = 'M720';
update qsforms set ExcelImport = 1 where FormCode = 'M316';

CREATE TABLE `qsfmailgroups` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`GroupID` INT NOT NULL ,
UNIQUE (
`FormCode` ,
`GroupID`
)
) ENGINE = InnoDB;

CREATE TABLE `qsfmails` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`Extra` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `FormCode` )
) ENGINE = InnoDB;

CREATE TABLE `qsfsmsgroups` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`GroupID` INT NOT NULL ,
UNIQUE (
`FormCode` ,
`GroupID`
)
) ENGINE = InnoDB;

CREATE TABLE `qsfsms` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`Extra` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `FormCode` )
) ENGINE = InnoDB;


ALTER TABLE `qsfields` ADD COLUMN `Unique` TINYINT(4) NOT NULL DEFAULT '0';
update qsfields set `Unique` = 1 where DefaultVal = 'UNIQUE' or DefaultVal = 'GUNIQUE';
update qsfields set `DefaultVal` = NULL where DefaultVal = 'UNIQUE' or DefaultVal = 'GUNIQUE'; 

/*nếu chưa có thì update, đã update cho các app trên server bản 3*/
ALTER TABLE `qsuserreports` ADD COLUMN `Mobile` TINYINT NOT NULL DEFAULT '0';
/*chỉ cho trường hợp đầu tiên, đã update cho các app trên server bản 3*/
delete from `qsuserreports`;
delete from qsuserreport;
insert into qsuserreport select 1, URID from qsuserreports;

//Mobile
ALTER TABLE `qsfields` ADD COLUMN `Mobile` TINYINT(4) NOT NULL DEFAULT '0' AFTER `Unique`;
Update qsfields set Mobile = Grid;
ALTER TABLE `qsforms` ADD COLUMN `classMobile` VARCHAR(50) NULL AFTER `Secure`;
Update qsforms set classMobile = class;

ALTER TABLE `qsworkflowsteps` ADD COLUMN `OrderNo` INT NULL AFTER `StepNo`;
update qsworkflowsteps set OrderNo = StepNo;

update qsfields set Grid = Grid | (if(Mobile,2,0));
ALTER TABLE `qsfields` DROP COLUMN `Mobile`;
update qsfobjects set Public = 3 where Public = 1;

REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`) VALUES (200, 'OVatTuPBT', 'M705', 'ODanhSachThietBi', 'MaThietBi', 'TenThietBi', 'MaThietBiKhac', 'MÃ£ tb khÃ¡c', 1, '', NULL, 1, NULL, 0, 4, 200, 0, '', '', 1, 0, '', '', '', '', 'Other Equip', 1, 0);
REPLACE INTO `qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`) VALUES (210, 'OVatTuPBT', 'M705', 'OCauTrucThietBi', 'ViTri', 'BoPhan', 'ViTriKhac', 'Vá»‹ trÃ­ láº¥y', 1, '', NULL, 1, NULL, 0, 3, 200, 0, '', '', 3, 0, '', '', '', '', 'Other position', 1, 0);
Update `qsfields` set FieldCode = 'SerialKhac', FieldName = 'Serial# thay', FieldName_en= 'Replace serial#' where FieldCode = 'ToSerial' and ObjectCode = 'OVatTuPBT';
update `qsfields` set Regx = '{"0":"Thay váº­t tÆ°","1":"Thay PT tá»« kho", "2":"Thay PT tá»« thiáº¿t bá»‹ khÃ¡c", "3":"Äá»•i PT tá»« thiáº¿t bá»‹ khÃ¡c",
 "4":"Láº¯p má»›i PT", "5":"ThÃ¡o PT"}' where FieldCode = 'HinhThuc' and ObjectCode = 'OVatTuPBT';
 update `qsfields` set Regx = '{"0":"Thay váº­t tÆ°","1":"Thay PT tá»« kho", "2":"Thay PT tá»« thiáº¿t bá»‹ khÃ¡c", "3":"Äá»•i PT tá»« thiáº¿t bá»‹ khÃ¡c", "4":"Láº¯p má»›i PT", "5":"ThÃ¡o PT"}' where FieldCode = 'HinhThuc' and ObjectCode = 'OVatTu';

ALTER TABLE `qsworkflowsteps`
	ADD COLUMN `Mix` TINYINT NOT NULL DEFAULT '0' COMMENT '1: Nhập lý do' AFTER `Name_en`;

ALTER TABLE qsuigroups
	ADD COLUMN Name_en VARCHAR(200) AFTER `Name`;
	
	update qsfields set
	FieldWidth = case when InputType = 2 then 205 when InputType = 3 and Regx != 'auto' then 220 when FieldType = 4 or FieldType = 10 
	then 100 when FieldType = 5 or FieldType = 6 then 50 else 200 end;
	update qsfields set
	FieldWidth = 100 where ObjectCode = 'OPhieuBaoTri' and FieldCode = 'LanBaoTri';

ALTER TABLE `qsfields` CHANGE COLUMN `RefLabel` `RefLabel` INT NULL DEFAULT NULL AFTER `DefaultVal`;
update qsfields set RefLabel = 10;

update qsfields set FieldWidth = 100 where FieldType = 9;

ALTER TABLE qsreports ADD COLUMN `TableWidth` INT AFTER `Name`;

CREATE TABLE `qschats`( `CID` INT(11) NOT NULL AUTO_INCREMENT, 
	`Title` TEXT, 
	`Sender` INT(11), 
	`Receiver` INT(11), 
	`Status` INT(1) COMMENT '0 unread, 1 read, 2 sent', 
	`TimeSend` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
	PRIMARY KEY (`CID`) ) 
	ENGINE=INNODB;

ALTER TABLE qsfcomment MODIFY `Comment` text;

UPDATE qsfields SET InputType = 4 WHERE InputType = 3;
UPDATE qsfields SET InputType = 4 WHERE InputType = 3 AND IFNULL(Regx, '') NOT IN ('auto', 'recalculate', '');

DROP TABLE IF EXISTS `qsfreader`;
CREATE TABLE IF NOT EXISTS `qsfreader` (
	`FRID` int(11) NOT NULL auto_increment,
  `IFID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `Date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`FRID`),
  UNIQUE KEY `IFID` (`IFID`,`UID`)
) ENGINE=INNODB;
ALTER TABLE `qsusers`
	ADD COLUMN `Notify` INT NULL DEFAULT NULL AFTER `SessionID`,
	ADD COLUMN `Message` INT NULL DEFAULT NULL AFTER `Notify`,
	ADD COLUMN `Event` INT NULL DEFAULT NULL AFTER `Message`;

ALTER TABLE `qsfields` ADD COLUMN `Style` VARCHAR(100);

update qsforms set Document = 2 where FormCode = 'M759';

Update qsfields SET `Unique` = 2 where ObjectCode = 'OLoaiThietBi' AND FieldCode = 'TenLoai';

ALTER TABLE `qsrecordrights`
	ADD COLUMN `Rights` INT NOT NULL DEFAULT '0';
update qsrecordrights set Rights = 31;


CREATE TABLE `qsfvalidategroups` (
`FormCode` VARCHAR( 40 ) NOT NULL ,
`GroupID` INT NOT NULL ,
UNIQUE (
`FormCode` ,
`GroupID`
)
) ENGINE = InnoDB;

update `qsworkflowsteps` set `Color` = 'bgbegin' where `SID`=98;
update `qsworkflowsteps` set `Color` = 'bgaqua' where `SID`=101;
update `qsworkflowsteps` set `Color` = 'bgforestgreen white' where `SID`=141;
update `qsworkflowsteps` set `Color` = 'bgred white' where `SID`=142;