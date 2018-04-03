<?php
class Qss_Bin_Onload_OPhieuSuCo extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
        
		parent::__doExecute();
        
		/*$user = Qss_Register::get('userinfo');
		$rights = Qss_Lib_System::getFormRights('M707', $user->user_group_list);
		if($rights && !($rights & 2))
		{
			$dvbt = $this->_object->getFieldByCode('MaDVBT');
			$dvbt->arrFilters[] = sprintf(' v.IFID_M125 in (select IFID_M125 from ONhanVien
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
						where Ref_TenTruyCap = %1$d)',
					$user->user_id);
		}*/
		/*if($this->_object->intStatus == 1)
		{
			$KhuVuc = $this->_object->getFieldByCode('KhuVuc')->getValue();
			$this->_object->getFieldByCode('MaKhuVuc')->bReadOnly = true;
			$this->_object->getFieldByCode('MaThietBi')->bReadOnly = true;
			$this->_object->getFieldByCode('BoPhan')->bReadOnly = true;
			if($KhuVuc)
			{
				$this->_object->getFieldByCode('MaKhuVuc')->bReadOnly = false;
				$this->_object->getFieldByCode('MaKhuVuc')->bRequired = true;
				$this->_object->getFieldByCode('MaThietBi')->setRefIOID(0);
				$this->_object->getFieldByCode('TenThietBi')->setRefIOID(0);
				$this->_object->getFieldByCode('TenThietBi')->szRegx = "auto";
			}
			else 
			{
				$this->_object->getFieldByCode('MaThietBi')->bRequired = true;
				$this->_object->getFieldByCode('MaThietBi')->bReadOnly = false;
				$this->_object->getFieldByCode('BoPhan')->bReadOnly = false;
				$this->_object->getFieldByCode('MaKhuVuc')->setRefIOID(0);
				//$this->_object->getFieldByCode('MaKhuVuc')->szRegx = "auto";
			}
		}*/
	}
}