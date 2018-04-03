<?php
class Qss_Bin_Calculate_ODSDonMuaHang_SoLuong extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanPham->SoLuongMua(1)?$this->OSanPham->SoLuongMua(1):1;
	}
}
?>