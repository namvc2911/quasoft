<?php
class Qss_Bin_Calculate_OGiaoDich_PhanTramCoHoi extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OLoaiGiaoDich->PhanTramCoHoi(1);
	}
}
?>