<?php
class Qss_Bin_Calculate_OXuatKho_TenThietBi extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $phieu = (int)$this->_object->getFieldByCode('PhieuBaoTri')->getRefIOID();
        $ret   = '';



        if($phieu)
        {
            $sql = sprintf('
            SELECT * 
            FROM OPhieuBaoTri
            WHERE IOID = %1$d
            ', (int)$phieu);
            $dat  = $this->_db->fetchOne($sql);

            if($dat)
            {
                $ret = "{$dat->MaThietBi} - {$dat->TenThietBi}";
            }
        }
        else
            {
        $sql = sprintf('SELECT * 
            FROM OPhieuBaoTri
            inner join OXuatKho on OXuatKho.Ref_PhieuBaoTri = OPhieuBaoTri.IOID  
            WHERE OXuatKho.IOID = %1$d
            ', (int)$this->_object->i_IOID);
                $dat  = $this->_db->fetchOne($sql);

                if($dat)
                {
                    $ret = "{$dat->MaThietBi} - {$dat->TenThietBi}";
                }
            }

        return $ret;
    }
}
?>