<?php
class Qss_Bin_Calculate_OBanDienDotXuat_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->SanLuong(1) * $this->DonGia(1);
	}
}
?>