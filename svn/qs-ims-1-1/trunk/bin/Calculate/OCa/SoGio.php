<?php
class Qss_Bin_Calculate_OCa_SoGio extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$start = Qss_Lib_Date::formatTime($this->GioBatDau(1));
		$end   = Qss_Lib_Date::formatTime($this->GioKetThuc(1));
		$mcStart = strtotime($start);
		$mcEnd   = strtotime($end);
		$mcMinus = $mcEnd - $mcStart;

		if($mcMinus <= 0) // bat dau tu ngay hom truoc sang ngay hom sau
		{
			$today = date('d-m-Y'); // ngay hien tai gia dinh
			$tomorrow = date("d-m-Y", time()+86400); // ngay mai gia dinh 
			$todayTime = $today .' '. $start;
			$tomorrowTime = $tomorrow .' '. $end;
			$mcMinus = strtotime($tomorrowTime) - strtotime($todayTime);
		}
		// celse // cung trong mot ngay, khong thay doi gi

		return abs(round(10*($mcMinus) /3600)/10);
	}
}
?>