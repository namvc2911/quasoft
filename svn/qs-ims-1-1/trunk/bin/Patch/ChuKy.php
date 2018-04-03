<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');

$sql = "update OChuKyBaoTri
inner join OBaoTriDinhKy on OBaoTriDinhKy.IFID_M724 = OChuKyBaoTri.IFID_M724  
set Thu = WEEKDAY(NgayBatDau)+1, Ngay = day(NgayBatDau), Thang = month(NgayBatDau) 
where DieuChinhTheoPBT = 1";
$db->execute($sql);

$sql = "update OChuKyBaoTri
set ChuKy = case 
when KyBaoDuong='Hàng ngày' then if(LapLai=1,'Hàng ngày',concat(LapLai,' ngày một lần'))
when KyBaoDuong='Hàng tuần' then if(LapLai=1,concat('Thứ ',Thu,' Hàng tuần'),concat(LapLai,' tuần một lần vào thứ ',Thu))
when KyBaoDuong='Hàng tháng' then if(LapLai=1,concat('Ngày ',Ngay,' hàng tháng'),concat(LapLai,' tháng một lần vào ngày ',Ngay))
when KyBaoDuong='Hàng năm' then if(LapLai=1,concat('Ngày ',Ngay,'/',Thang,' hàng năm'),concat(LapLai,' năm một lần vào ngày ',Ngay,'/',Thang))
end
where ifnull(ChuKy,'') = ''";
$db->execute($sql);

$sql = "update OChuKyBaoTri
set 
Ref_KyBaoDuong = KyBaoDuong,
KyBaoDuong = case 
when KyBaoDuong='Hàng ngày' then 'D'
when KyBaoDuong='Hàng tuần' then 'W'
when KyBaoDuong='Hàng tháng' then 'M'
when KyBaoDuong='Hàng năm' then 'Y'
end
where concat('',Ref_KyBaoDuong * 1) = Ref_KyBaoDuong";
$db->execute($sql);

$sql = "update OPhieuBaoTri as pbt
inner join OChuKyBaoTri as chuky on chuky.IOID = pbt.Ref_ChuKy
inner join OBaoTriDinhKy as btdk on btdk.IFID_M724 =  chuky.IFID_M724
set pbt.MoTa = btdk.MoTa,pbt.Ref_MoTa = btdk.IOID
where ifnull(pbt.Ref_MoTa,0) = 0";
$db->execute($sql);

$sql = sprintf('update OChuKyBaoTri set ChuKy = REPLACE(ChuKy,%1$s,%2$s)'
	,$db->quote('Thứ Thứ')
	,$db->quote('Thứ'));
$db->execute($sql);

$sql = sprintf('update OPhieuBaoTri set ChuKy = REPLACE(ChuKy,%1$s,%2$s)'
	,$db->quote('Thứ Thứ')
	,$db->quote('Thứ'));
$db->execute($sql);
?>