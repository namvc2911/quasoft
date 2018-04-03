<?php
class Qss_Bin_Calculate_OLichThietBi_MaKhuVuc extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODanhSachThietBi->MaKhuVuc(1);
	}
}
?>