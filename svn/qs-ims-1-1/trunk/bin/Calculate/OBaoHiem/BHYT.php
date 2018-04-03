<?php
class Qss_Bin_Calculate_OBaoHiem_BHYT extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_BHYT    = $SYSTEM_PARAMS->getById('BHYT');
		return ($this->MucLuong(1) * $SYSTEM_BHYT->Value/100);
	}
}
?>