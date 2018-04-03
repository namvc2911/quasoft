<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');

$sql = "update OPhieuBaoTri
set MoTa = ifnull(ChuKy,LoaiBaoTri) 
where ifnull(MoTa,'') = ''";
$db->execute($sql);

$sql = "update OBaoTriDinhKy
inner join OBaoTriDinhKy_Old on OBaoTriDinhKy_Old.IFID_M724 = OBaoTriDinhKy.IFID_M724
set OBaoTriDinhKy.MoTa = LoaiBaoTri 
where ifnull(OBaoTriDinhKy.MoTa,'') = ''";
$db->execute($sql);
?>