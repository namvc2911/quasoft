<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OThanhToan_NgayThanhToan extends Qss_Lib_Style
{
    public function __doExecute()
    {
  	  	$compare = Qss_Lib_Date::compareTwoDate($this->_data->NgayThanhToan, date('Y-m-d'));
		$diff = Qss_Lib_Date::diffTime($this->_data->NgayThanhToan, date('Y-m-d'),'D');
		$bg = '';
		if($this->_data->Status <= 1 && $this->_data->NgayThanhToan)
		{
			if($compare <= 0)
			{
				if($diff >= 45)
				{
					$bg = 'bgpink';
				}
				elseif($diff >= 30)
				{
					$bg = 'bgyellow';
				}
				elseif($diff >= 15)
				{
					$bg = 'bgaqua';
				}
			}
			
		}
		return $bg;
    }

}
?>