<?php
class Qss_Bin_Calculate_ONhanHangTL_SoChungTu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return Qss_Lib_Extra::getDocumentNo($this->_object);
	}
}
?>