<?php
class Qss_Bin_Calculate_ODieuChinhLuong_PhanTram extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OLoaiDieuChinhLuong->PhanTramMacDinh(1);
	}
}
?>