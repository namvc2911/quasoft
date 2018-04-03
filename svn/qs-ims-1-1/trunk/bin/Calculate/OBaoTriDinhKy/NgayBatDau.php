<?php
class Qss_Bin_Calculate_OBaoTriDinhKy_NgayBatDau extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return '01-01-'.date('Y');
	}
}
?>
