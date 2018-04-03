<?php
/**
* Copy từ công việc sang
 */
class Qss_Bin_Bash_CopyTaskToMaterial extends Qss_Lib_Bin
{
	public function __doExecute()
	{
        $ifid = $this->_form->i_IFID;

        if($ifid) 
        {
            $sql = sprintf('insert ignore into OVatTuPBTDK(IFID_M759,HinhThuc,Ref_HinhThuc,CongViec,Ref_CongViec,ViTri,Ref_ViTri,BoPhan,Ref_BoPhan, Ngay)
            	select IFID_M759,"0","Thay vật tư",MoTa,IOID,ViTri,Ref_ViTri,BoPhan,Ref_BoPhan, Ngay
            	from OCongViecBTPBT where IFID_M759 = %1$d
            	and not exists (select 1 from OVatTuPBTDK where IFID_M759 = %1$d)',$ifid);
            $this->_db->execute($sql);
        }
	}
	
}