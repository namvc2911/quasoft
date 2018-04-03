<?php
class Qss_Bin_Calculate_OThanhPhanSanPham_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->SoLuong(1) * $this->GiaThanh(1);
	}
}
?>