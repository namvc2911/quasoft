<?php
class Qss_Bin_Calculate_OQuanLyChamCong_BatDau extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OCa->GioBatDau(1);
	}
}
?>