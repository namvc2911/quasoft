<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_Ton extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $maTaiSan     = (int)$this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $maNhanVien   = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $phieuBanGiao = (int)$this->_object->getFieldByCode('PhieuBanGiao')->intRefIOID;

        if(!$maTaiSan && !$maNhanVien && !$phieuBanGiao)
        {
            $sql = sprintf('SELECT * FROM OChiTietThuHoiTaiSan WHERE IOID = %1$d', $this->_object->i_IOID);
            $dat = $this->_db->fetchOne($sql);

            if($dat)
            {
                $maTaiSan     = (int)$dat->Ref_MaTaiSan;
                $maNhanVien   = (int)$dat->Ref_MaNhanVien;
                $phieuBanGiao = (int)$dat->Ref_PhieuBanGiao;
            }
        }

        if(!$phieuBanGiao)
        {
            if($maTaiSan && $maNhanVien)
            {
                $where        = '';
                $where       .= $maNhanVien?sprintf(' AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = %1$d ', $maNhanVien):' AND 1=0 ';
                $where       .= $maTaiSan?sprintf(' AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = %1$d ', $maTaiSan):'';
                $sql          = sprintf('
                SELECT 
                    OPhieuBanGiaoTaiSan.IOID
                    , OPhieuBanGiaoTaiSan.SoPhieu
                    , ((IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0)) 
                    - SUM(IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0))) AS `Rest`
                FROM OPhieuBanGiaoTaiSan 
                INNER JOIN qsiforms AS IFormBanGiao ON OPhieuBanGiaoTaiSan.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                
                LEFT JOIN OChiTietThuHoiTaiSan ON OPhieuBanGiaoTaiSan.IOID = IFNULL(OChiTietThuHoiTaiSan.Ref_PhieuBanGiao, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVien, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0)
                LEFT JOIN qsiforms as IFormThHoi ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = IFormThHoi.IFID
                
                WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
                GROUP BY OPhieuBanGiaoTaiSan.IFID_M182
                HAVING `Rest` > 0
                ORDER BY OPhieuBanGiaoTaiSan.SoPhieu ASC
                LIMIT 1
                ', $where
                );
                // echo '<pre>'; print_r($sql); die;
                $first        = $this->_db->fetchOne($sql);

                if($first)
                {
                    $phieuBanGiao = $first->IOID;
                }
            }
        }

        $sql = sprintf('
            SELECT SUM( IFNULL(`Rest`, 0) ) AS `Rest`
            FROM 
            (
                SELECT 
                ((IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0)) 
                - SUM(IF(IFNULL(IFormThHoi.Status, 0) = 2, IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0), 0))) AS `Rest`
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN qsiforms AS IFormBanGiao ON OPhieuBanGiaoTaiSan.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182               
                LEFT JOIN OChiTietThuHoiTaiSan ON OPhieuBanGiaoTaiSan.IOID = IFNULL(OChiTietThuHoiTaiSan.Ref_PhieuBanGiao, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVien, 0)
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0)
                LEFT JOIN qsiforms as IFormThHoi ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = IFormThHoi.IFID
                WHERE IFNULL(IFormBanGiao.Status, 0) = 2
                    AND OPhieuBanGiaoTaiSan.IOID = %3$d
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) = %1$d
                    AND IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) = %2$d
                GROUP BY OPhieuBanGiaoTaiSan.IOID            
            ) AS t1
            

        ', $maNhanVien, $maTaiSan, $phieuBanGiao);
        // echo '<Pre>'; print_r($sql); die;
        $dat = $this->_db->fetchOne($sql);

        return $dat?Qss_Lib_Util::formatNumber($dat->Rest):0;
    }
}
?>