<?php
class Qss_Bin_Calculate_ODanhGiaNanglucNV_NangLucNhanVien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$point  = 0;
		$weight = 0;
		$arr = $this->OChiSoDanhGiaNV(0);
		foreach ($arr as $item)
		{
			$point  += $item->Dat;
			$weight += $item->TrongSo;
		}
		return $weight?($point/$weight):0;
	}
}
?>