<?php
class Qss_Model_M705_Equipment extends Qss_Model_Abstract
{
    /**
     * Lấy cấu trúc của một thiết bị sắp xếp theo lft (Hình cây)
     * @Note: Có một hàm tương tự là getSparepartOfEquip trong Qss_Model_Maintenance_Equipment
     */
    public function getStructuresOfEquip($eqIOID) {
        $sql = sprintf('
            SELECT 
                CauTruc.*
                , MatHang.DacTinhKyThuat AS DacTinhKyThuatMatHang
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
            LEFT JOIN OSanPham AS MatHang ON CauTruc.Ref_MaSP = MatHang.IOID
            WHERE ThietBi.IOID = %1$d
            ORDER BY CauTruc.lft
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách cách tài liệu được gắn vào một thiết bị
     * @Note: Có một hàm tương tự là getDocumentsOfEquip trong Qss_Model_Maintenance_Equipment
     * @param $eqIOID
     * @return mixed
     */
    public function getDocumentsOfEquip($eqIOID) {
        $sql = sprintf('
            SELECT qsdocumenttype.Type, count(1) AS Total
            FROM ODanhSachThietBi
            INNER JOIN qsfdocuments ON ODanhSachThietBi.IFID_M705 = qsfdocuments.IFID
            INNER JOIN qsdocumenttype ON qsfdocuments.DTID = qsdocumenttype.DTID
            WHERE ODanhSachThietBi.IOID = %1$d
            GROUP BY qsdocumenttype.DTID
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách đặc tính kỹ thuật theo một thiết bị
     * @Note: Có một hàm tương tự là getTechnicalParameters trong Qss_Model_Maintenance_Equipment, tuy nhiên hàm này truyền
     * vào là array IOID thiết bị
     * @param array $IOIDarray
     * @return mixed
     */
    public function getTechSpecificationsOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT TSTB.*, DSTB.IOID AS TBIOID 
            FROM ODanhSachThietBi as DSTB
            INNER JOIN ODacTinhThietBi as TSTB ON DSTB.IFID_M705 = TSTB.IFID_M705
            WHERE DSTB.IOID = %1$d
            ORDER BY TSTB.IFID_M705
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);

    }
    
	public function countByGroup()
    {
        $sql = sprintf('
            SELECT Ref_NhomThietBi, NhomThietBi, count(*) as TongSo 
            FROM ODanhSachThietBi
            GROUP BY Ref_NhomThietBi
            ORDER BY TongSo DESC
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);

    }
}