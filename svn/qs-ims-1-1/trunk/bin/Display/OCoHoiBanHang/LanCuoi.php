<?php
class Qss_Bin_Display_OCoHoiBanHang_LanCuoi extends Qss_Lib_Style
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$retval  = 0;
		$table = Qss_Model_Db::Table('OGiaoDich');
		$table->where(sprintf('IFID_M504=%1$d',$this->_data->IFID_M504));
		$table->orderby('Ngay desc, IOID desc limit 1');
		$item     = $table->fetchOne();
		$enddate = @$item->Ngay;
		$retval  = @$item->NoiDung;
		$diff = Qss_Lib_Date::divDate($enddate, date('Y-m-d'));
		$time = '';
		if($diff == 0)
		{
			$time = sprintf('(Hôm nay): ');
		}
		elseif($diff < 30)
		{
			$time = sprintf('(%1$d ngày): ',$diff);
		}
		elseif($diff < 365)
		{
			$time = sprintf('(%1$d tháng): ',(int)($diff/30));
		}
		elseif($diff >= 365)
		{
			$time = sprintf('(%1$d năm): ',(int)($diff/365));
		}
		return '<strong>'.$time.'</strong>' . $retval;
	}
}
?>