
<?php
class Qss_Bin_Calculate_ODanhSachCK_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->DonGia(1) * $this->SoLuong(1);
	}
}
?>