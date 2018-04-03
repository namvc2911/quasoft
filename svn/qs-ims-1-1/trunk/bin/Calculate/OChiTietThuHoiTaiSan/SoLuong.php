<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_SoLuong extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $emp   = $this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $asset = $this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $ho    = $this->_object->getFieldByCode('PhieuBanGiao')->intRefIOID;

        $sql = sprintf('
                SELECT OChiTietBanGiaoTaiSan.SoLuong
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN (SELECT * FROM OPhieuThuHoiTaiSan WHERE IFID_M183 = %4$d) AS PhieuThuHoi
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                WHERE OPhieuBanGiaoTaiSan.IOID = %1$d AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = %2$d 
                    AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = %3$d
            ', $ho, $emp, $asset, $this->_object->i_IFID);
        $dat  = $this->_db->fetchOne($sql);

        return $dat?$dat->SoLuong:'';
    }
}
?>