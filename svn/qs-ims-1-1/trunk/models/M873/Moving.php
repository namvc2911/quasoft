<?php
class Qss_Model_M873_Moving extends Qss_Model_Abstract {
    public function countThietBiDuAn($strDuAn, $strThietBi = '') {
        $sql = sprintf('
            SELECT count(1) as Total
            FROM ODanhSachThietBi AS ThietBi                                                  
            WHERE ThietBi.DuAn = %1$s 
        ', $this->_o_DB->quote($strDuAn));
        $sql .= $strThietBi?sprintf(' AND (ThietBi.MaThietBi like "%%%1$s%%" OR ThietBi.TenThietBi like "%%%1$s%%") ', $strThietBi):'';
        $data = $this->_o_DB->fetchOne($sql);

        // echo '<pre>'; print_r($sql); die;
        return $data?$data->Total:0;
    }

    public function getThietBiByDuAn($strDuAn, $IFID_M873, $page, $display, $strThietBi = '') {
        $sql = sprintf('
            SELECT 
                ThietBi.*
                , DanhSachThietBiDatCho.SoPhieuDatCho
                , IFNULL(PhieuHienTai.SoPhieu, "") AS SoPhieuHienTai
            FROM ODanhSachThietBi AS ThietBi  
            LEFT JOIN (
                SELECT 
                    OThietBiDieuDongVe.*
                    , GROUP_CONCAT(DISTINCT SoPhieu ORDER BY SoPhieu ASC SEPARATOR ", ") AS SoPhieuDatCho
                FROM ODieuDongThietBiVe
                INNER JOIN qsiforms ON ODieuDongThietBiVe.IFID_M873 = qsiforms.IFID
                INNER JOIN OThietBiDieuDongVe ON ODieuDongThietBiVe.IFID_M873 = OThietBiDieuDongVe.IFID_M873                
                WHERE ODieuDongThietBiVe.IFID_M873 != %2$d AND ODieuDongThietBiVe.DuAn = %1$s AND qsiforms.Status IN (1,2)
                GROUP BY OThietBiDieuDongVe.MaThietBi
            ) AS DanhSachThietBiDatCho ON ThietBi.IOID = DanhSachThietBiDatCho.Ref_MaThietBi  
            LEFT JOIN (
                SELECT 
                    OThietBiDieuDongVe.*, ODieuDongThietBiVe.SoPhieu
                FROM ODieuDongThietBiVe
                INNER JOIN OThietBiDieuDongVe ON ODieuDongThietBiVe.IFID_M873 = OThietBiDieuDongVe.IFID_M873                
                WHERE ODieuDongThietBiVe.IFID_M873 = %2$d 
            ) AS PhieuHienTai ON ThietBi.IOID = PhieuHienTai.Ref_MaThietBi  
            WHERE ThietBi.DuAn = %1$s 
        ', $this->_o_DB->quote($strDuAn), $IFID_M873);
        $sql .= $strThietBi?sprintf(' AND (ThietBi.MaThietBi like "%%%1$s%%" OR ThietBi.TenThietBi like "%%%1$s%%") ', $strThietBi):'';
        $sql .= ' ORDER BY ThietBi.MaThietBi   ';
        $sql .= sprintf(' LIMIT %1$d, %2$d ', ceil(( abs($page-1) * $display)), $display);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}