<?php
class Qss_Bin_Calculate_OLichThietBi_LichLamViec extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODanhSachThietBi->LichLamViec(1);
	}
}
?>