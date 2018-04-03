<?php
include "../syslogin.php";
$db      = Qss_Db::getAdapter('main');
$mImport = new Qss_Model_Import_Form('M759',false, false);

// Bên này sẽ chạy ra phiếu bảo trì từ kế hoạch tổng thể
$sql = sprintf('
	SELECT OPhieuBaoTri.IFID_M759, OPhieuBaoTri.SoPhieu, OVatTuKeHoach.*
	FROM OPhieuBaoTri AS OPhieuBaoTri		
	INNER JOIN OKeHoachBaoTri AS OKeHoachBaoTri ON OPhieuBaoTri.Ref_NgayKeHoach = OKeHoachBaoTri.IOID
	INNER JOIN OVatTuKeHoach AS OVatTuKeHoach ON OKeHoachBaoTri.IFID_M837 = OVatTuKeHoach.IFID_M837
	WHERE IFNULL(OPhieuBaoTri.Ref_SoKeHoach, 0) != 0 AND IFNULL(OPhieuBaoTri.Ref_NgayKeHoach, 0) != 0
		AND (SELECT COUNT(1) FROM OVatTuPBTDK WHERE IFID_M759 = OPhieuBaoTri.IFID_M759) = 0
		AND OPhieuBaoTri.SoPhieu like "%%03.2018.%%"
	ORDER BY OPhieuBaoTri.IFID_M759
');

$data   = $db->fetchAll($sql);
$insert = array();
$old    = '';
$i      = 0;

foreach($data as $item) {
    if($old != $item->IFID_M759) {
        $i      = 0; // reset

        $insert[$item->IFID_M759]['OPhieuBaoTri'][0]['SoPhieu'] = $item->SoPhieu;
    }

    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['CongViec']     = @$item->CongViec; // Có thể không còn công việc này nữa cần kiểm tra trường hợp này
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['ViTri']        = @(int)$item->Ref_ViTri;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['BoPhan']       = @(int)$item->Ref_ViTri;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['MaVatTu']      = @(int)$item->Ref_MaVatTu;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['Ref_TenVatTu'] = @(int)$item->Ref_MaVatTu;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['TenVatTu']     = @$item->TenVatTu;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['DonViTinh']    = @(int)$item->Ref_DonViTinh;
    $insert[$item->IFID_M759]['OVatTuPBTDK'][$i]['SoLuong']      = $item->SoLuongDuKien;

    $i++;

    $old = $item->IFID_M759;
}

if(count($insert)) {
    foreach($insert as $key=>$item) {
        $mImport->setData($item);
    }

    $mImport->generateSQL();
}



