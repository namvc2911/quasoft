<?php
class Qss_Bin_Calculate_ODSBGBanHang_DonGia extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanPham->GiaMua(1);
	}
}
?>