<?php
class Qss_Bin_Calculate_OCTBangLuong_SoTienDongBHYT extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_BHYT    = $SYSTEM_PARAMS->getById('BHYT');
		return ($this->LuongCoBan(1) * $SYSTEM_BHYT->Value/100);
	}
}
?>