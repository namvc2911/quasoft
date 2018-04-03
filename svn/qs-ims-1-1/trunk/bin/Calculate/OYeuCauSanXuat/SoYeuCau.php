<?php
class Qss_Bin_Calculate_OYeuCauSanXuat_SoYeuCau extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return Qss_Lib_Extra::getDocumentNo($this->_object);
	}
}
?>