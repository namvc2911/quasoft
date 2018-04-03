<?php
class Qss_Bin_Calculate_OQuanLyChamCong_KetThuc extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OCa->GioKetThuc(1);
	}
}
?>