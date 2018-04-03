<?php
class Qss_Bin_Calculate_OChiTietBanGiaoTaiSan_ThoiGianDaSuDung extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $emp   = $this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $asset = $this->_object->getFieldByCode('MaTaiSan')->intRefIOID;

        $sql = sprintf('
                SELECT OChiTietThuHoiTaiSan.ThoiGianDaSuDung
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN OPhieuThuHoiTaiSan ON IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = OPhieuThuHoiTaiSan.IOID
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
                WHERE OPhieuBanGiaoTaiSan.IFID_M182 = %1$d AND OChiTietThuHoiTaiSan.Ref_MaNhanVien = %2$d 
                    AND OChiTietThuHoiTaiSan.Ref_MaTaiSan = %3$d
            ', $this->_object->i_IFID, $emp, $asset);
        $dat  = $this->_db->fetchOne($sql);

        return $dat?$dat->ThoiGianDaSuDung:'';
    }
}
?>