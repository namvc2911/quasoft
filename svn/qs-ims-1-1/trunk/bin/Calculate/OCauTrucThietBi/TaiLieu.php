<?php
class Qss_Bin_Calculate_OCauTrucThietBi_TaiLieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		if($this->_object->i_IOID)
		{
			return sprintf('<a href="#" onclick="objectDocument(%1$d,%2$d,%3$d)">Tài liệu</a>'
					,$this->_object->i_IFID
					,$this->_object->intDepartmentID
					,$this->_object->i_IOID);
		}
		else 
		{
			return '';
		}
	}
}
?>