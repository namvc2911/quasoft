
<?php
class Qss_Bin_Calculate_ODanhSachHangTL_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->SoLuong(1) * $this->DonGia(1);
	}
}
?>