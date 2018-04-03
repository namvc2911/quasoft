<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_ThoiGianDaSuDung extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $emp   = $this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
        $asset = $this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $ho    = $this->_object->getFieldByCode('PhieuBanGiao')->intRefIOID;


        if($asset && $emp)
        {
            $where        = '';
            $where       .= $emp?sprintf(' AND ChiTiet.Ref_MaNhanVien = %1$d ', $emp):' AND 1=0 ';
            $where       .= $asset?sprintf(' AND ChiTiet.Ref_MaTaiSan = %1$d ', $asset):'';
            $first        = $this->_db->fetchOne(sprintf('
            SELECT Phieu.*
            FROM OPhieuBanGiaoTaiSan AS Phieu
            INNER JOIN qsiforms AS IForm ON Phieu.IFID_M182 = IForm.IFID
            INNER JOIN OChiTietBanGiaoTaiSan AS ChiTiet ON Phieu.IFID_M182 = ChiTiet.IFID_M182
            WHERE IFNULL(IForm.Status, 0) = 2 %1$s
            GROUP BY Phieu.IFID_M182
            ORDER BY Phieu.SoPhieu ASC
            LIMIT 1
        ', $where
            ));

            if(!$ho && $first)
            {
                $ho = $first->IOID;
            }
        }


        {
            $sql = sprintf('
                SELECT 
                    CEIL((TIMESTAMPDIFF(DAY, OPhieuBanGiaoTaiSan.Ngay, PhieuThuHoi.Ngay)/30)*10)/10 + IFNULL(OChiTietBanGiaoTaiSan.ThoiGianDaSuDung, 0) AS ThoiGianDaSuDungKhiThuHoi
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN (SELECT * FROM OPhieuThuHoiTaiSan WHERE IFID_M183 = %4$d) AS PhieuThuHoi
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                WHERE OPhieuBanGiaoTaiSan.IOID = %1$d AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = %2$d 
                    AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = %3$d
            ', $ho, $emp, $asset, $this->_object->i_IFID);
            $dat  = $this->_db->fetchOne($sql);

            return $dat?$dat->ThoiGianDaSuDungKhiThuHoi:'';
        }

    }
}
?>