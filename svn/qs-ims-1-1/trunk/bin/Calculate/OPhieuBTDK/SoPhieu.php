<?php
class Qss_Bin_Calculate_OPhieuBTDK_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return Qss_Lib_Extra::getDocumentNo($this->_object);
	}
}
?>