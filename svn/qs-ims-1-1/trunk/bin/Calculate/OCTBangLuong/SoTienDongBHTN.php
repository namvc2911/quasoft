<?php
class Qss_Bin_Calculate_OCTBangLuong_SoTienDongBHTN extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_BHTN    = $SYSTEM_PARAMS->getById('BHTN');
		return ($this->LuongCoBan(1) * $SYSTEM_BHTN->Value/100);
	}
}
?>