<?php
class Qss_Bin_Style_OCoHoiBanHang_NgayTiepTheo extends Qss_Lib_Style
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$bg = 'center';
		$date = Qss_Lib_Date::displaytomysql($this->_data->NgayTiepTheo);
		$compare = Qss_Lib_Date::compareTwoDate($date, date('Y-m-d'));
		if($date)
		{
			if($compare < 0)
			{
				$bg .= ' bgpink';
			}
			elseif($compare == 0)
			{
				$bg .= ' bgaqua';
			}
		}
		return $bg;
	}
}
?>