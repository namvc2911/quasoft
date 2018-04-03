<?php
class Qss_Bin_Calculate_OCoHoiBanHang_NgayKetThuc extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$retval  = 0;
		$arr     = $this->OGiaoDich(0);
		$enddate = 0;
		
		foreach ($arr as $item)
		{
			if(Qss_Lib_Date::compareTwoDate($item->Ngay, $enddate))
			{
				$enddate = $item->Ngay;
				$retval  = $item->Ngay;
			}
		}
		return $retval;
	}
}
?>