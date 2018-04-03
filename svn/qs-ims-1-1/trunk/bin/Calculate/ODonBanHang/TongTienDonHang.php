
<?php
class Qss_Bin_Calculate_ODonBanHang_TongTienDonHang extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return (($this->TongTien(1) - $this->GiamTru(1)) + $this->CPVanChuyen(1));
	}
}
?>