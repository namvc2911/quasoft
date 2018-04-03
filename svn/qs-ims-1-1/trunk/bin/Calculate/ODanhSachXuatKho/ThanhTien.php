
<?php
class Qss_Bin_Calculate_ODanhSachXuatKho_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return round($this->DonGia(1) * $this->SoLuong(1),0);
	}
}
?>