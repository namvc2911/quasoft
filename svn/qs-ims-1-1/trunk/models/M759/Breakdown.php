<?php
class Qss_Model_M759_Breakdown extends Qss_Model_Abstract
{
    /**
     * Chọn tất cả phiếu đã đóng với loại bảo trì là loại sự cố theo một thiết bị (Không phân biệt phòng ban)
     * @param $eqID
     * @return mixed
     */
    public function getClosedBreakdownByEquip($eqID)
    {
        $sql = sprintf('
			SELECT
				pbt.*, CauTruc.ViTri, CauTruc.BoPhan                
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS qsiforms ON qsiforms.IFID = pbt.IFID_M759
			LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(pbt.Ref_BoPhan, 0) = CauTruc.IOID
			LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = qsiforms.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND qsiforms.Status = qsws.StepNo
			WHERE IFNULL(qsw.Actived, 0) = 1 
			    -- AND qsiforms.Status = 4  
			    and pbt.Ref_MaThietBi = %1$d 
			    and plbt.LoaiBaoTri = "%2$s"'
            /* ORDER BY */
            , $eqID, Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN);
        return $this->_o_DB->fetchAll($sql);
    }
}