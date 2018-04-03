<?php
class Qss_Bin_Filter_MNotify extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and (qsiforms.UID = %1$d or (qsiforms.Status =2 and v.IFID_C005 in (SELECT IFID_C005 FROM MNotifyUser
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = MNotifyUser.Ref_MaNV 
						WHERE ODanhSachNhanVien.Ref_TenTruyCap = %1$d)))'
				,$this->_user->user_id);
		return $retval;
	}
}