<?php
class Qss_Bin_Calculate_ODieuKhoanThanhToan_SoTienThanhToan extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODonBanHang->TongTienDonHang(1) * ($this->PhanTramThanhToan(1)/100);
	}
}
?>