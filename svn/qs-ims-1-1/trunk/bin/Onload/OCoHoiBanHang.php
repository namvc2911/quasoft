<?php
class Qss_Bin_Onload_OCoHoiBanHang extends Qss_Lib_Onload
{
    public function __doExecute()
    {
		parent::__doExecute();
    }

    public function KhuVuc()
    {
		if(Qss_Lib_System::formSecure('M848'))
		{
			$user   = Qss_Register::get('userinfo');
			$this->_object->getFieldByCode('KhuVuc')->arrFilters[] = sprintf(' v.IFID_M848 in (SELECT IFID FROM 
						qsrecordrights WHERE FormCode = "M848" and UID = %1$d)'
				,$user->user_id);
		}
	}
	public function NhanVien()
    {
		$this->_object->getFieldByCode('NhanVien')->arrFilters[] = sprintf(' ifnull(v.ThoiViec,0) = 0');
	}
	

}