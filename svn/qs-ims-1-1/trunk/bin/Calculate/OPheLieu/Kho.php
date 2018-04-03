<?php
class Qss_Bin_Calculate_OPheLieu_Kho extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ONVLDauVao->OCauThanhSanPham->KhoPheLieu(1);
	}
}
?>