<?php
class Qss_Model_M876_Main extends Qss_Model_Abstract {
    public function getData($projectIOID) {
        $sql = sprintf('
            SELECT 
                OYeuCauTrangThietBi.*
                , OYeuCauTrangThietBiVatTu.SoPhieu
                , OSuCo.HuHong
                , OSuCo.Mat
                , Mua.SoLuongMua 
                , DieuDong.SoLuongDieuDong
            FROM OYeuCauTrangThietBiVatTu
            INNER JOIN qsiforms ON OYeuCauTrangThietBiVatTu.IFID_M751 = qsiforms.IFID
            INNER JOIN OYeuCauTrangThietBi ON OYeuCauTrangThietBiVatTu.IFID_M751 = OYeuCauTrangThietBi.IFID_M751
            LEFT JOIN (
                SELECT count(1) as SoLuongMua, Ref_LoaiThietBi, Ref_SoYeuCau
                FROM ODanhSachThietBi
                WHERE IFNULL(ODanhSachThietBi.Ref_DuAnMua, 0) = %1$d
                GROUP BY Ref_LoaiThietBi
            ) AS Mua ON OYeuCauTrangThietBi.Ref_LoaiThietBi = Mua.Ref_LoaiThietBi 
                AND OYeuCauTrangThietBiVatTu.IOID = Mua.Ref_SoYeuCau
            LEFT JOIN (
                SELECT OLichThietBi.Ref_PhieuYeuCau, ODanhSachThietBi.Ref_LoaiThietBi, COUNT(1) as SoLuongDieuDong
                FROM OLichThietBi
                INNER JOIN qsiforms ON OLichThietBi.IFID_M706 = qsiforms.IFID
                INNER JOIN ODanhSachDieuDongThietBi ON OLichThietBi.IFID_M706 = ODanhSachDieuDongThietBi.IFID_M706
                INNER JOIN ODanhSachThietBi On ODanhSachDieuDongThietBi.Ref_MaThietBi = ODanhSachThietBi.IOID
                WHERE qsiforms.Status = 3
                GROUP BY ODanhSachThietBi.Ref_LoaiThietBi
            ) AS DieuDong ON OYeuCauTrangThietBi.Ref_LoaiThietBi = DieuDong.Ref_LoaiThietBi 
                AND OYeuCauTrangThietBiVatTu.IOID = DieuDong.Ref_PhieuYeuCau
            
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
            WHERE IFNULL(OYeuCauTrangThietBiVatTu.Ref_DuAn, 0) = %1$d AND qsiforms.Status = 3
        ', $projectIOID);

        //echo '<pre>'; print_r($sql);die;
        return $this->_o_DB->fetchAll($sql);
    }
}