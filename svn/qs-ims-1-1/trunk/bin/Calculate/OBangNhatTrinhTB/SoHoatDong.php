<?php
class Qss_Bin_Calculate_OBangNhatTrinhTB_SoHoatDong extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return ($this->SoCuoi(1) - $this->SoDau(1));
	}
}
?>