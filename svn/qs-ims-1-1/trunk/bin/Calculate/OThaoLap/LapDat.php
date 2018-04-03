<?php
class Qss_Bin_Calculate_OThaoLap_LapDat extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODanhSachPhuTung->DaLapGiap(1)?0:1;
	}
}
?>