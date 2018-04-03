<?php
class Qss_Bin_Calculate_OCoHoiBanHang_PhanTramCoHoi extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$retval  = 0;
		$arr     = $this->OGiaoDich(0);
		$maxIoid = 0;
		
		foreach ($arr as $item)
		{
			if($item->IOID > $maxIoid)
			{
				$maxIoid = $item->IOID;
				$retval  = $item->PhanTramCoHoi;
			}
		}
		return $retval;
	}
}
?>