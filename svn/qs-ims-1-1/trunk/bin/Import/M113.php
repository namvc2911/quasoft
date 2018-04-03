<?php
class Qss_Bin_Import_M113 extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $sql = sprintf('					
                insert into ODonViTinhSP(IFID_M113,DeptID,DonViTinh,Ref_DonViTinh,HeSoQUyDoi,MacDinh)
                select IFID_M113, DeptID, DonViTinh, Ref_DonViTinh, 1, 1 
                from OSanPham 
                where IFID_M113 not in (select IFID_M113 from ODonViTinhSP )									
                ');
        $this->_db->execute($sql);
         $sql = sprintf('					
                update ODonViTinhSP
                inner join OSanPham on OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 and MacDinh=1
                set  ODonViTinhSP.DonViTinh = OSanPham.DonViTinh, ODonViTinhSP.Ref_DonViTinh = OSanPham.Ref_DonViTinh 
                where ODonViTinhSP.Ref_DonViTinh != OSanPham.Ref_DonViTinh');
        $this->_db->execute($sql);
    }

}