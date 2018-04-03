<?php
class Qss_Bin_Calculate_ODieuChinhLuong_SoTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OLoaiDieuChinhLuong->GiaTriMacDinh(1);
	}
}
?>