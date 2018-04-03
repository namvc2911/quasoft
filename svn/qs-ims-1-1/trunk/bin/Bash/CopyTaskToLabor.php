<?php
/**
 * Copy từ công việc sang
 */
class Qss_Bin_Bash_CopyTaskToLabor extends Qss_Lib_Bin
{
	public function __doExecute()
	{
        $ifid = $this->_form->i_IFID;

        if($ifid) 
        {
            $sql = sprintf('insert ignore into ONhanCongPBT(IFID_M759,CongViec,Ref_CongViec,NhanVien,Ref_NhanVien)
            	select IFID_M759,MoTa,IOID,NguoiThucHien,Ref_NguoiThucHien
            	from OCongViecBTPBT where IFID_M759 = %1$d
            	and not exists (select 1 from ONhanCongPBT where IFID_M759 = %1$d)',$ifid);
            $this->_db->execute($sql);
        }
	}
	
}