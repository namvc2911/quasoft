<?php
class Qss_Bin_Calculate_OBin_DonViTinh extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanPham->DonViTinh(1);
	}
}
?>