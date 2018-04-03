<?php
class Qss_Bin_Calculate_OSanPhamLoi_Kho extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OThongKeSanLuong->OCauThanhSanPham->KhoPheLieu(1);
	}
}
?>