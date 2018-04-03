<?php
class Qss_Bin_Calculate_OGiaoDich_GiaTriDuTinh extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return ($this->OCoHoiBanHang->GiaTri(1) * $this->PhanTramCoHoi(1) ) / 100;
	}
}
?>