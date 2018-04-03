
<?php
class Qss_Bin_Calculate_OThuTien_SoTienThanhToan extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OHoaDonMuaHang->TongTienHD(1) * ($this->PhanTramThanhToan(1)/100);
	}
}
?>