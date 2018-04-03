<?php
class Qss_Bin_Filter_OThongBao extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' 
		    and 
		    (
		        qsiforms.UID = %1$d 
		        or 
		        (
		            qsiforms.Status =2 and v.IFID_M856 in 
                    (
                        SELECT IFID_M856 
                        FROM ONhanVienNhanThongBao
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVienNhanThongBao.Ref_MaNV 
						WHERE ODanhSachNhanVien.Ref_TenTruyCap = %1$d
                    )
                )
            )'
				,$this->_user->user_id);
		return $retval;
	}
}