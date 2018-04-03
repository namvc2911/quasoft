<?php
class Qss_Model_M874_Main extends Qss_Model_Abstract 
{
    public function getData($projectIOID,$ycccIOID=0) 
    {
    	$where = '';
   		if($ycccIOID)
		{
			$where .= sprintf(' and OYeuCauTrangThietBiVatTu.IOID = %1$d',$ycccIOID); 
		}
        $sql = sprintf('
            SELECT 
                OYeuCauTrangThietBi.*
                , OYeuCauTrangThietBiVatTu.SoPhieu
                , OSuCo.HuHong
                , OSuCo.Mat
            FROM OYeuCauTrangThietBiVatTu
            INNER JOIN qsiforms ON OYeuCauTrangThietBiVatTu.IFID_M751 = qsiforms.IFID
            INNER JOIN OYeuCauTrangThietBi ON OYeuCauTrangThietBiVatTu.IFID_M751 = OYeuCauTrangThietBi.IFID_M751
            LEFT JOIN (
                SELECT 
                    ODanhSachThietBi.Ref_LoaiThietBi
                    , ODanhSachThietBi.LoaiThietBi
                    , SUM(CASE WHEN IFNULL(OYeuCauBaoTri.SuCo, 0) = 1 OR IFNULL(OYeuCauBaoTri.SuCo, 0) = 2 THEN 1 ELSE 0 END) AS HuHong
                    , SUM(CASE WHEN IFNULL(OYeuCauBaoTri.SuCo, 0) = 3 THEN 1 ELSE 0 END) AS Mat
                FROM OYeuCauBaoTri 
                INNER JOIN ODanhSachThietBi ON OYeuCauBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                WHERE IFNULL(OYeuCauBaoTri.Ref_DuAn, 0) = %1$d
                GROUP BY ODanhSachThietBi.Ref_LoaiThietBi
            ) AS OSuCo ON OYeuCauTrangThietBi.Ref_LoaiThietBi = OSuCo.Ref_LoaiThietBi
            WHERE IFNULL(OYeuCauTrangThietBiVatTu.Ref_DuAn, 0) = %1$d AND qsiforms.Status = 3 %2$s
        ', $projectIOID
        , $where);
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }
}