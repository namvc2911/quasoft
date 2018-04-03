<?php
class Qss_Bin_Calculate_OChiTietBanGiaoTaiSan_NhaMay extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $maNhanVien = (int)$this->_object->getFieldByCode('MaNhanVien')->intRefIOID;

        if(!$maNhanVien)
        {
            $sql = sprintf('SELECT * FROM OChiTietBanGiaoTaiSan WHERE IOID = %1$d', $this->_object->i_IOID);
            $dat = $this->_db->fetchOne($sql);

            if($dat)
            {
                $maNhanVien = (int)$dat->Ref_MaNhanVien;
            }
        }

        $sql = sprintf('SELECT * FROM ODanhSachNhanVien WHERE IOID = %1$d', $maNhanVien);
        $dat = $this->_db->fetchOne($sql);

        return $dat?$dat->TenPhongBan:'';
    }
}
?>