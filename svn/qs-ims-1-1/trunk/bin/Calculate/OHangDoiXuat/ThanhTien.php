<?php
class Qss_Bin_Calculate_OHangDoiXuat_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$qty       = $this->SoLuong(1);
		$unitPrice = $this->DonGia(1)?$this->DonGia(1):0;	
		return 	$qty*$unitPrice;
	}
}
?>