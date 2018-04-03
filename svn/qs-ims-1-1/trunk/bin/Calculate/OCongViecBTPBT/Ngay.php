<?php
class Qss_Bin_Calculate_OCongViecBTPBT_Ngay extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        return $this->OPhieuBaoTri->NgayBatDau(1);
	}
}
?>