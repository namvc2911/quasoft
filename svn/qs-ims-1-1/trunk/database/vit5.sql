update OPhieuBaoTri set NgayBatDauDuKien = NgayYeuCau where ifnull(NgayBatDauDuKien,'') = '';
update qsstepgroups set Rights = if((Rights&4),Rights|48,Rights);
ALTER TABLE `qsworkflowsteps` ADD COLUMN `QuickStep` VARCHAR(50) NULL DEFAULT NULL;