<?php
class Qss_Bin_Calculate_ONhomPhuCap_TongTienPhuCap extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$arr    = $this->OPhuCap(0);
		foreach ($arr as $item)
		{
			$retval += $item->SoTienPhuCap;
		}
		return  $retval/1000;
	}
}
?>