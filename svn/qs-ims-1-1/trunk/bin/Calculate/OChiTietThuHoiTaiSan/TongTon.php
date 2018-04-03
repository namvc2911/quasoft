<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_TongTon extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $maTaiSan     = (int)$this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $maNhanVien   = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;

        if(!$maTaiSan && !$maNhanVien)
        {
            $sql = sprintf('SELECT * FROM OChiTietThuHoiTaiSan WHERE IOID = %1$d', $this->_object->i_IOID);
            $dat = $this->_db->fetchOne($sql);

            if($dat)
            {
                $maTaiSan     = (int)$dat->Ref_MaTaiSan;
                $maNhanVien   = (int)$dat->Ref_MaNhanVien;
            }
        }

        $sql = sprintf('
            SELECT SUM(`Rest`) AS `Rest`
            FROM
            (
                SELECT ((IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0)) 
                - SUM(IF(IFNULL(IFormThHoi.Status, 0) = 2, IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0), 0))) AS `Rest`
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN qsiforms AS IFormBanGiao ON OPhieuBanGiaoTaiSan.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182               
                LEFT JOIN OChiTietThuHoiTaiSan ON OPhieuBanGiaoTaiSan.IOID = IFNULL(OChiTietThuHoiTaiSan.Ref_PhieuBanGiao, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVien, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0)
                LEFT JOIN qsiforms as IFormThHoi ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = IFormThHoi.IFID
                WHERE IFNULL(IFormBanGiao.Status, 0) = 2                
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = %1$d
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = %2$d
                GROUP BY OPhieuBanGiaoTaiSan.IOID, OChiTietBanGiaoTaiSan.Ref_MaNhanVien, OChiTietBanGiaoTaiSan.Ref_MaTaiSan
            ) AS table1
          
            

        ', $maNhanVien, $maTaiSan);
        $dat = $this->_db->fetchOne($sql);

        return $dat?Qss_Lib_Util::formatNumber($dat->Rest):0;
    }
}
?>