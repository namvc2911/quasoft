<?php
class Qss_Bin_Import_M759 extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $sql = sprintf('					
                update OVatTuPBT
                inner join OSanPham on OSanPham.IOID = OVatTuPBT.Ref_MaVatTu
                set  OVatTuPBT.ChiPhi = ifnull(OSanPham.GiaMua,0) * ifnull(OVatTuPBT.SoLuong,0) 
                where IFID_M759 = %1$d',$this->_form->i_IFID);
        $this->_db->execute($sql);
    }

}