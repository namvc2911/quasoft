<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_NgayBanGiao extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $phieuBanGiao = (int)$this->_object->getFieldByCode('PhieuBanGiao')->intRefIOID;

        if(!$phieuBanGiao)
        {
            $sql = sprintf('SELECT * FROM OChiTietThuHoiTaiSan WHERE IOID = %1$d', $this->_object->i_IOID);
            $dat = $this->_db->fetchOne($sql);

            if($dat)
            {
                $phieuBanGiao = (int)$dat->Ref_PhieuBanGiao;
            }
        }

        $sql = sprintf('
            SELECT Ngay
            FROM OPhieuBanGiaoTaiSan            
            WHERE  IOID = %1$d', $phieuBanGiao);
        $dat = $this->_db->fetchOne($sql);

        return $dat?$dat->Ngay:'';
    }
}
?>