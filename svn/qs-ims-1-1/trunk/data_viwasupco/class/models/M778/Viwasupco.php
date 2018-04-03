<?php
class Qss_Model_M778_Viwasupco extends Qss_Model_Abstract
{
    public function getPlansByEquip($equipIOID) {
        $retval = array();

        $sql = sprintf('
            SELECT 
                OBaoTriDinhKy.*
                , OChuKyBaoTri.IOID AS Ref_ChuKy
                , OChuKyBaoTri.ChuKy
                , OCauTrucThietBi.ViTri
                , OCauTrucThietBi.BoPhan
                , OVatTu.Ref_MaVatTu
                , OVatTu.MaVatTu
                , OVatTu.TenVatTu
                , OVatTu.DonViTinh
                , OVatTu.SoLuong
            FROM OBaoTriDinhKy
            INNER JOIN OChuKyBaoTri ON OBaoTriDinhKy.IFID_M724 = OChuKyBaoTri.IFID_M724
            LEFT JOIN OCauTrucThietBi ON IFNULL(OBaoTriDinhKy.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
            LEFT JOIN OVatTu ON OBaoTriDinhKy.IFID_M724 = OVatTu.IFID_M724
            WHERE IFNULL(OBaoTriDinhKy.Ref_MaThietBi, 0) = %1$d            
            ORDER BY OBaoTriDinhKy.IFID_M724, OChuKyBaoTri.IOID
        ', $equipIOID);

        // echo '<pre>'; print_r($sql); die;

        $data = $this->_o_DB->fetchAll($sql);

        $oldM724  = '';
        $oldChuKy = '';
        $i        = -1;

        foreach($data as $item) {
            if($oldM724 != $item->IFID_M724) { // Sang mot dong moi
                $i++;
                $retval[$i] = $item;
                $retval[$i]->TongHopChuKy = '';
                $retval[$i]->TongHopVatTu = '';
                $retval[$i]->TongHopVatTuExcel = '';

            }

            if($oldChuKy != $item->Ref_ChuKy) { // Chi cong them chu ky khi co chu ky moi
                $retval[$i]->TongHopChuKy .= $retval[$i]->TongHopChuKy?', ':'';
                $retval[$i]->TongHopChuKy .= $item->ChuKy;
            }

            if($item->Ref_MaVatTu) {
                $retval[$i]->TongHopVatTu .= $retval[$i]->TongHopVatTu?'<br/> ':'';
                $retval[$i]->TongHopVatTu .= "- {$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";

                $retval[$i]->TongHopVatTuExcel .= $retval[$i]->TongHopVatTuExcel?', ':'';
                $retval[$i]->TongHopVatTuExcel .= "{$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";
            }

            $oldM724  = $item->IFID_M724;
            $oldChuKy = $item->Ref_ChuKy;
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    /**
     * Chọn tất cả phiếu đã đóng với loại bảo trì là loại sự cố theo một thiết bị (Không phân biệt phòng ban)
     * @param $eqID
     * @return mixed
     */
    public function getBreakdownsByEquip($eqID)
    {
        $retval = array();

        // Không dùng group concat vì muốn dùng chung number format của qss lib bin
        $sql = sprintf('
			SELECT
				pbt.*
				, CauTruc.ViTri
				, CauTruc.BoPhan
				, VatTu.Ref_MaVatTu
				, VatTu.MaVatTu
				, VatTu.TenVatTu
				, VatTu.DonViTinh
				, VatTu.SoLuong
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS qsiforms ON qsiforms.IFID = pbt.IFID_M759
			LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(pbt.Ref_BoPhan, 0) = CauTruc.IOID
			LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = qsiforms.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND qsiforms.Status = qsws.StepNo
			LEFT JOIN OVatTuPBT AS VatTu ON pbt.IFID_M759 = VatTu.IFID_M759
			WHERE IFNULL(qsw.Actived, 0) = 1 
			    -- AND qsiforms.Status = 4  
			    and pbt.Ref_MaThietBi = %1$d 
			    and plbt.LoaiBaoTri = "%2$s"
            ORDER BY pbt.IFID_M759'
            /* ORDER BY */
            , $eqID, Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
        $data = $this->_o_DB->fetchAll($sql);

        $oldM759  = '';

        $i        = -1;

        foreach($data as $item) {
            if($oldM759 != $item->IFID_M759) {
                $i++;
                $retval[$i] = $item;
                $retval[$i]->TongHopVatTu = '';
                $retval[$i]->TongHopVatTuExcel = '';

            }

            if($item->Ref_MaVatTu) {
                $retval[$i]->TongHopVatTu .= $retval[$i]->TongHopVatTu?'<br/> ':'';
                $retval[$i]->TongHopVatTu .= "- {$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";

                $retval[$i]->TongHopVatTuExcel .= $retval[$i]->TongHopVatTuExcel?', ':'';
                $retval[$i]->TongHopVatTuExcel .= "{$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";
            }

            $oldM759 = $item->IFID_M759;
        }

        return $retval;
    }

    public function getPreventiveByEquip($eqID)
    {
        $retval = array();
        $sql = sprintf('
			SELECT
				pbt.*
				, CauTruc.ViTri
				, CauTruc.BoPhan
				, VatTu.Ref_MaVatTu
				, VatTu.MaVatTu
				, VatTu.TenVatTu
				, VatTu.DonViTinh
				, VatTu.SoLuong				  
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS qsiforms ON qsiforms.IFID = pbt.IFID_M759
			LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(pbt.Ref_BoPhan, 0) = CauTruc.IOID
			LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = qsiforms.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND qsiforms.Status = qsws.StepNo
			LEFT JOIN OVatTuPBT AS VatTu ON pbt.IFID_M759 = VatTu.IFID_M759
			WHERE IFNULL(qsw.Actived, 0) = 1 
			    -- AND qsiforms.Status = 4  
			    and pbt.Ref_MaThietBi = %1$d 
			    and plbt.LoaiBaoTri != "%2$s"'
            /* ORDER BY */
            , $eqID, Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
        $data = $this->_o_DB->fetchAll($sql);

        $oldM759  = '';

        $i        = -1;

        foreach($data as $item) {
            if($oldM759 != $item->IFID_M759) {
                $i++;
                $retval[$i] = $item;
                $retval[$i]->TongHopVatTu = '';
                $retval[$i]->TongHopVatTuExcel = '';
            }

            if($item->Ref_MaVatTu) {
                $retval[$i]->TongHopVatTu .= $retval[$i]->TongHopVatTu?'<br/> ':'';
                $retval[$i]->TongHopVatTu .= "- {$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";

                $retval[$i]->TongHopVatTuExcel .= $retval[$i]->TongHopVatTuExcel?', ':'';
                $retval[$i]->TongHopVatTuExcel .= "{$item->TenVatTu} ({$item->MaVatTu}): ".Qss_Lib_Util::formatNumber($item->SoLuong)." {$item->DonViTinh}";
            }

            $oldM759 = $item->IFID_M759;
        }

        return $retval;
    }
}