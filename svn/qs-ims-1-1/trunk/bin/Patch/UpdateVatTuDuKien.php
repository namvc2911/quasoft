<?php
    // Viết cho Viwasupco, update vật tư dự kiến từ vật tư thực tế.
    include "../syslogin.php";
    $db       = Qss_Db::getAdapter('main');
    $mImport  = new Qss_Model_Import_Form('M759', false, false, 1, 1);
    $insert   = array();
    $i        = 0;
    $oldPhieu = '';

    // Không insert các phiếu có vật tư dự kiến
    $sql = "
        SELECT OVatTuPBT.*, OPhieuBaoTri.SoPhieu 
        FROM OPhieuBaoTri
        INNER JOIN OVatTuPBT ON OPhieuBaoTri.IFID_M759 = OVatTuPBT.IFID_M759
        WHERE IFNULL(OPhieuBaoTri.IFID_M759, 0) NOT IN (SELECT IFNULL(IFID_M759, 0) AS IFID FROM OVatTuPBTDK)
        ORDER BY OPhieuBaoTri.IFID_M759
    ";
    $dataSQL = $db->fetchAll($sql);

    foreach($dataSQL as $item) {
        if($oldPhieu != $item->IFID_M759) {
            if(count($insert)) { // Cho phiếu trước đó
                $mImport->setData($insert);
            }

            $insert = array();  // Reset
            $i      = 0;        // Reset
            $insert['OPhieuBaoTri'][0]['SoPhieu'] = $item->SoPhieu;
        }

        $insert['OVatTuPBTDK'][$i]['CongViec']  = (int)@$item->Ref_CongViec;
        $insert['OVatTuPBTDK'][$i]['ViTri']     = (int)@$item->Ref_ViTri;
        $insert['OVatTuPBTDK'][$i]['BoPhan']    = (int)@$item->Ref_ViTri;
        $insert['OVatTuPBTDK'][$i]['MaVatTu']   = (int)@$item->Ref_MaVatTu;
        $insert['OVatTuPBTDK'][$i]['TenVatTu']  = $item->TenVatTu;
        $insert['OVatTuPBTDK'][$i]['DonViTinh'] = (int)@$item->Ref_DonViTinh;
        $insert['OVatTuPBTDK'][$i]['SoLuong']   = $item->SoLuong;
        $i++;

        $oldPhieu = $item->IFID_M759;
    }

    if(count($insert)) { // Cho phiếu trước đó
        $mImport->setData($insert);
    }

    $mImport->generateSQL();
    $error = $mImport->countFormError() + $mImport->countObjectError();

    if($error) {
        echo 'Error: '.$error; die;
    }
?>