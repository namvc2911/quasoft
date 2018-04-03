<?php
include "../sysbase.php";
$db = Qss_Db::getAdapter('main');

//Update số lượng hiện có trong kho
$sql = "
UPDATE OXuatKho
INNER JOIN OPhieuBaoTri ON IFNULL(OXuatKho.Ref_PhieuBaoTri, 0) = OPhieuBaoTri.IOID
SET OXuatKho.DonViThucHien = OPhieuBaoTri.MaDVBT, OXuatKho.Ref_DonViThucHien = OPhieuBaoTri.Ref_MaDVBT
WHERE IFNULL(OPhieuBaoTri.Ref_MaDVBT, 0) <> 0";

$db->execute($sql);
?>