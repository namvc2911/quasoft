<?php
class Qss_Bin_Calculate_OChiSoDanhGiaNV_Dat extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return ($this->TrongSo(1) * $this->Diem(1));
	}
}
?>