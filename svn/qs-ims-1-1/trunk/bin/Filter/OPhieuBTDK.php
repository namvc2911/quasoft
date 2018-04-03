<?php
class Qss_Bin_Filter_OPhieuBTDK extends Qss_Lib_Filter
{
	public function getJoin()
	{
		$retval = '';
		$rights = Qss_Lib_System::getFormRights('M706', $this->_user->user_group_list);
		if($rights && !($rights & 16))
		{
			//select 
			$retval = sprintf(' 
					inner join ODanhSachThietBi on v.Ref_MaThietBi = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					');
		}
		return $retval;
	}
	public function getWhere()
	{
		$retval = '';
		$rights = Qss_Lib_System::getFormRights('M706', $this->_user->user_group_list);
		if($rights && !($rights & 16))
		{
			//select 
			$retval = sprintf(' and ODanhSachNhanVien.Ref_TenTruyCap = %1$d',$this->_user->user_id);
		}
		return $retval;
	}
}