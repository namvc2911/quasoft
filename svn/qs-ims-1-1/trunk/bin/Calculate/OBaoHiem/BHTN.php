<?php
class Qss_Bin_Calculate_OBaoHiem_BHTN extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_BHTN    = $SYSTEM_PARAMS->getById('BHTN');
		return ($this->MucLuong(1) * $SYSTEM_BHTN->Value/100);
	}
}
?>