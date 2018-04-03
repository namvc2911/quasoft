<?php
class Qss_Bin_Onload_OKeHoachDungMay extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		if($this->_object->intStatus == 1)
		{
			$KhuVuc = $this->_object->getFieldByCode('KhuVuc')->getValue();
			$this->_object->getFieldByCode('MaKhuVuc')->bReadOnly = true;
			$this->_object->getFieldByCode('MaThietBi')->bReadOnly = true;
			if($KhuVuc)
			{
				$this->_object->getFieldByCode('MaKhuVuc')->bReadOnly = false;
				$this->_object->getFieldByCode('MaKhuVuc')->bRequired = true;
				$this->_object->getFieldByCode('MaThietBi')->setRefIOID(0);
				$this->_object->getFieldByCode('TenThietBi')->setRefIOID(0);
				$this->_object->getFieldByCode('MaThietBi')->szRegx = "auto";
			}
			else 
			{
				$this->_object->getFieldByCode('MaThietBi')->bRequired = true;
				$this->_object->getFieldByCode('MaThietBi')->bReadOnly = false;
				$this->_object->getFieldByCode('MaKhuVuc')->setRefIOID(0);
				$this->_object->getFieldByCode('TenKhuVuc')->setRefIOID(0);
				$this->_object->getFieldByCode('MaKhuVuc')->szRegx = "auto";
			}
		}
	}
	
}