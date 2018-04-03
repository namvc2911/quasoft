<?php
class Qss_Bin_Calculate_ONVLDauVao_Kho extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanXuat->ODayChuyen->KhoNVL(1);
	}
}
?>