<?php
class Qss_Bin_Calculate_OBaoHiem_BHXH extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_BHXH    = $SYSTEM_PARAMS->getById('BHXH');
		return ($this->MucLuong(1) * $SYSTEM_BHXH->Value/100);
	}
}
?>