<?php
class Qss_Bin_Onload_OChiTietBanGiaoTaiSan   extends Qss_Lib_Onload
{
    public function __doExecute()
    {
        parent::__doExecute();
    }

    public function MaNhanVien()
    {
        $sql  = sprintf('SELECT * FROM OPhieuBanGiaoTaiSan WHERE IFID_M182 = %1$d ', $this->_object->i_IFID);
        $dat  = $this->_db->fetchOne($sql);

        if($dat && $dat->PhieuThuHoi)
        {
            $this->_object->getFieldByCode('MaNhanVien')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT ChiTiet.Ref_MaNhanVienMoi
                FROM OPhieuThuHoiTaiSan AS Phieu
                INNER JOIN OChiTietThuHoiTaiSan AS ChiTiet ON Phieu.IFID_M183 = ChiTiet.IFID_M183
                WHERE Phieu.IOID = %1$d
                GROUP BY ChiTiet.Ref_MaNhanVienMoi
                ORDER BY ChiTiet.MaNhanVienMoi
            )', $dat->Ref_PhieuThuHoi);
        }
    }

    /**
     * Loc cac tai san ban giao theo phieu ban giao va ma nhan vien
     * Neu khong co ma nhan vien va phieu ban giao thi khong cho chon tai san
     */
    public function MaTaiSan()
    {
        $sql        = sprintf('SELECT * FROM OPhieuBanGiaoTaiSan WHERE IFID_M182 = %1$d ', $this->_object->i_IFID);
        $dat        = $this->_db->fetchOne($sql);
        $maNhanVien = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;

        if($dat && $dat->PhieuThuHoi)
        {
            $this->_object->getFieldByCode('MaTaiSan')->arrFilters[] = sprintf('
            v.IOID in
            (
                SELECT ChiTiet.Ref_MaTaiSan
                FROM OPhieuThuHoiTaiSan AS Phieu
                INNER JOIN OChiTietThuHoiTaiSan AS ChiTiet ON Phieu.IFID_M183 = ChiTiet.IFID_M183
                WHERE Phieu.IOID = %1$d 
                    AND IFNULL(ChiTiet.Ref_MaNhanVienMoi, 0) != 0
                    AND IFNULL(ChiTiet.Ref_MaNhanVienMoi, 0) = %2$d
                GROUP BY ChiTiet.Ref_MaTaiSan
                ORDER BY ChiTiet.MaTaiSan
            )', $dat->Ref_PhieuThuHoi, $maNhanVien);
        }
    }

}