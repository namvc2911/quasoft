update OCauTrucThietBi ct
inner join ODanhSachPhuTung pt on pt.IFID_M705 = ct.IFID_M705
and pt.Ref_ViTri = ct.IOID
set ct.MaSP = pt.MaSP,
ct.Ref_MaSP = pt.Ref_MaSP,
ct.TenSP = pt.TenSP,
ct.Ref_TenSP = pt.Ref_TenSP,
ct.DonViTinh = pt.DonViTinh,
ct.Ref_DonViTinh = pt.Ref_DonViTinh,
ct.SoLuongChuan = pt.SoLuongChuan,
ct.SoLuongHC = pt.SoLuongHC,
ct.SoNgayCanhBao = pt.SoNgayCanhBao;

update OCauTrucThietBi set PhuTung = 1 where ifnull(Ref_MaSP,0) != 0;

----------------------------------
-- Update Danh Sach Diem Do
-- Truoc do can chuyen cac truong tu OChuKyKiemTraDiemDo Sang ODanhSachDiemDo va index crv ODanhSachDiemDo
-- Doi lookup cua nhat trinh thiet bi, nhap chi so tu dong sau index crv , ONhatTrinhThietBi, ONhapChiSoTuDong

UPDATE ODanhSachDiemDo AS A
INNER JOIN ODanhSachDiemDo_Old AS B ON A.IOID = B.IOID
INNER JOIN
(
	SELECT *
	FROM (SELECT * FROM OChuKyKiemTraDiemDo ORDER BY IOID DESC) AS C
	GROUP BY IFID_M754
) AS D ON B.IFID_M754 = D.IFID_M754
SET
	A.Ky = D.Ky
	, A.Ref_Ky = D.Ref_Ky
	, A.LapLai = D.LapLai
	, A.Thu = D.Thu
	, A.Ref_Thu = D.Ref_Thu
	, A.Ngay = D.Ngay
	, A.Ref_Ngay = D.Ref_Ngay
	, A.Thang = D.Thang
	, A.Ref_Thang = D.Ref_Thang
	, A.GhiChu = D.GhiChu;

UPDATE ODanhSachDiemDo AS A
INNER JOIN ODanhSachDiemDo_Old AS B ON A.IOID = B.IOID
INNER JOIN ODanhSachThietBi AS C ON B.Ref_MaThietBi = C.IOID
SET A.IFID_M705 = C.IFID_M705;


UPDATE ONhatTrinhThietBi AS A
INNER JOIN ONhatTrinhThietBi_Old AS A1 ON A.IOID = A1.IOID
INNER JOIN ODanhSachDiemDo_Old AS B1 ON A1.Ref_DiemDo = B1.IOID
INNER JOIN ODanhSachDiemDo AS B ON B1.IOID = B.IOID
SET A.Ref_DiemDo = B.IOID;

UPDATE ONhapChiSoTuDong AS A
INNER JOIN ONhapChiSoTuDong_Old AS A1 ON A.IOID = A1.IOID
INNER JOIN ODanhSachDiemDo_Old AS B1 ON A1.Ref_DiemDo = B1.IOID
INNER JOIN ODanhSachDiemDo AS B ON B1.IOID = B.IOID
SET A.Ref_DiemDo = B.IOID;

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

update internal.qsmenu as a
inner join pm3.qsmenu as b on a.MenuID = b.MenuID
set a.Icon = b.Icon
where ifnull(b.Icon,'') != '';

replace into anphatplastic.qsfields select * from pm3.qsfields where (ObjectCode = 'OCongViecBT' or ObjectCode = 'OCongViecBTPBT' or ObjectCode = 'OVatTu' or ObjectCode = 'OVatTuPBT');

update pm3.OBaoTriDinhKy set MoTa = LoaiBaoTri where ifnull(MoTa,'') = '';
ALTER TABLE `qsuiboxs` ADD `Hidden` TINYINT NOT NULL DEFAULT '0';

-- update chu kỳ
update OChuKyBaoTri as v
inner join OKy as t on t.IOID = v.Ref_KyBaoDuong
set v.KyBaoDuong = t.MaKy, v.Ref_KyBaoDuong = t.TenKy;
update OChuKyBaoTri as v
inner join OThu as t on t.IOID = v.Ref_Thu
set v.Thu = t.GiaTri, v.Ref_Thu = t.Thu;
update OChuKyBaoTri as v
inner join ONgay as t on t.IOID = v.Ref_Ngay
set v.Ngay = t.Ngay, v.Ref_Ngay = t.Ngay;
update OChuKyBaoTri as v
inner join OThang as t on t.IOID = v.Ref_Thang
set v.Thang = t.Thang, v.Ref_Thang = t.Thang;

update ODanhSachDiemDo as v
inner join OKy as t on t.IOID = v.Ref_Ky
set v.Ky = t.MaKy, v.Ref_Ky = t.TenKy;
update ODanhSachDiemDo as v
inner join OThu as t on t.IOID = v.Ref_Thu
set v.Thu = t.GiaTri, v.Ref_Thu = t.Thu;
update ODanhSachDiemDo as v
inner join ONgay as t on t.IOID = v.Ref_Ngay
set v.Ngay = t.Ngay, v.Ref_Ngay = t.Ngay;
update ODanhSachDiemDo as v
inner join OThang as t on t.IOID = v.Ref_Thang
set v.Thang = t.Thang, v.Ref_Thang = t.Thang;

ALTER TABLE `qsfields` ADD COLUMN `Unique` TINYINT(4) NOT NULL DEFAULT '0';
update namuga.qsfields set `Unique` = 1 where DefaultVal = 'UNIQUE' or DefaultVal = 'GUNIQUE';
update namuga.qsfields set `DefaultVal` = NULL where DefaultVal = 'UNIQUE' or DefaultVal = 'GUNIQUE'; 

--nếu chưa có thì update, đã update cho các app trên server bản 3
ALTER TABLE `qsuserreports` ADD COLUMN `Mobile` TINYINT NOT NULL DEFAULT '0';
--chỉ cho trường hợp đầu tiên, đã update cho các app trên server bản 3
delete from pm3.`qsuserreports`;
INSERT INTO pm3.`qsuserreports` (`URID`, `FormCode`, `Name`, `Params`, `Active`, `Name_en`, `Mobile`) VALUES (1, 'M759', 'Tá»· lá»‡ phiáº¿u Ä‘á»™t xuáº¥t', '/dashboard/pmradio', 0, 'Plan/Unplan WO ratio', 0)
,(2, 'M759', 'Tá»· lá»‡ phiáº¿u Ä‘á»™t xuáº¥t thÃ¡ng trÆ°á»›c', '/dashboard/pmradiolastmonth', 0, 'Plan/Unplan WO ratio last month', 0)
,(3, 'M759', 'Phiáº¿u báº£o trÃ¬ chÆ°a nghiá»‡m thu (column)', '/dashboard/openwocolumn', 0, 'Open work orders (column)', 0)
,(4, 'M759', 'Phiáº¿u báº£o trÃ¬ chÆ°a nghiá»‡m thu (pie)', '/dashboard/openwopie', 0, 'Open work orders (pie)', 0)
,(5, 'M759', 'Dá»«ng mÃ¡y', '/dashboard/breakdownmeter', 0, 'Downtime', 0)
,(6, 'M759', 'Dá»«ng mÃ¡y thÃ¡ng trÆ°á»›c', '/dashboard/breakdownmeterlastmonth', 0, 'Downtime last month', 0)
,(7, 'M759', 'Tá»· lá»‡ phiáº¿u Ä‘á»™t xuáº¥t', '/dashboard/pmradio', 0, 'Plan/Unplan WO ratio', 1)
,(8, 'M759', 'Tá»· lá»‡ phiáº¿u Ä‘á»™t xuáº¥t thÃ¡ng trÆ°á»›c', '/dashboard/pmradiolastmonth', 0, 'Plan/Unplan WO ratio last month', 1)
,(9, 'M759', 'Phiáº¿u báº£o trÃ¬ chÆ°a nghiá»‡m thu (column)', '/dashboard/openwocolumn', 0, 'Open work orders (column)', 1)
,(10, 'M759', 'Phiáº¿u báº£o trÃ¬ chÆ°a nghiá»‡m thu (pie)', '/dashboard/openwopie', 0, 'Open work orders (pie)', 1)
,(11, 'M759', 'Dá»«ng mÃ¡y', '/dashboard/breakdownmeter', 0, 'Downtime', 1)
,(12, 'M759', 'Dá»«ng mÃ¡y thÃ¡ng trÆ°á»›c', '/dashboard/breakdownmeterlastmonth', 0, 'Downtime last month', 1);
delete from pm3.qsuserreport;
insert into pm3.qsuserreport select 1, URID from qsuserreports;

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

REPLACE INTO eccbk.`qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`) VALUES (200, 'OVatTuPBT', 'M705', 'ODanhSachThietBi', 'MaThietBi', 'TenThietBi', 'MaThietBiKhac', 'MÃ£ tb khÃ¡c', 1, '', NULL, 1, NULL, 0, 4, 200, 0, '', '', 1, 0, '', '', '', '', 'Other Equip', 1, 0);
REPLACE INTO eccbk.`qsfields` (`FieldNo`, `ObjectCode`, `RefFormCode`, `RefObjectCode`, `RefFieldCode`, `RefDisplayCode`, `FieldCode`, `FieldName`, `FieldType`, `DefaultVal`, `RefLabel`, `Effect`, `DataType`, `ReadOnly`, `InputType`, `FieldWidth`, `Search`, `TValue`, `FValue`, `Grid`, `Required`, `AFunction`, `Pattern`, `PatternMessage`, `Regx`, `FieldName_en`, `isRefresh`, `Unique`) VALUES (210, 'OVatTuPBT', 'M705', 'OCauTrucThietBi', 'ViTri', 'BoPhan', 'ViTriKhac', 'Vá»‹ trÃ­ láº¥y', 1, '', NULL, 1, NULL, 0, 3, 200, 0, '', '', 3, 0, '', '', '', '', 'Other position', 1, 0);
Update eccbk.`qsfields` set FieldCode = 'SerialKhac', FieldName = 'Serial# thay', FieldName_en= 'Replace serial#' where FieldCode = 'ToSerial' and ObjectCode = 'OVatTuPBT';
update eccbk.`qsfields` set Regx = '{"0":"Thay váº­t tÆ°","1":"Thay PT tá»« kho", "2":"Thay PT tá»« thiáº¿t bá»‹ khÃ¡c", "3":"Äá»•i PT tá»« thiáº¿t bá»‹ khÃ¡c",
 "4":"Láº¯p má»›i PT", "5":"ThÃ¡o PT"}' where FieldCode = 'HinhThuc' and ObjectCode = 'OVatTuPBT';
 update eccbk.`qsfields` set Regx = '{"0":"Thay váº­t tÆ°","1":"Thay PT tá»« kho", "2":"Thay PT tá»« thiáº¿t bá»‹ khÃ¡c", "3":"Äá»•i PT tá»« thiáº¿t bá»‹ khÃ¡c", "4":"Láº¯p má»›i PT", "5":"ThÃ¡o PT"}' where FieldCode = 'HinhThuc' and ObjectCode = 'OVatTu';

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

-- UPDATE qsfields SET InputType = 4 WHERE InputType = 3;
UPDATE qsfields SET InputType = 4 WHERE InputType = 3 AND IFNULL(Regx, '') = '';
UPDATE qsfields SET InputType = 3 WHERE InputType = 4 AND IFNULL(Regx, '') != '';

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

ALTER TABLE `qsworkflowsteps` ADD COLUMN `QuickStep` VARCHAR(50) NULL DEFAULT NULL;

ALTER TABLE `qsfobjects` ADD COLUMN `RefFormCode` VARCHAR(40);