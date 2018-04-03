<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OHopDongBanHang_NgayKetThuc extends Qss_Lib_Style
{
    public function __doExecute()
    {
  	  	$compare = Qss_Lib_Date::compareTwoDate($this->_data->NgayKetThuc, date('Y-m-d'));
		$diff = Qss_Lib_Date::diffTime($this->_data->NgayKetThuc, date('Y-m-d'),'D');
		$bg = '';
		if($this->_data->Status == 1 && $this->_data->NgayKetThuc)
		{
			if($compare <= 0)
			{
				if($diff > 1)
				{
					$bg = 'bgpink center';
				}
				elseif($diff == 1)
				{
					$bg = 'bgaqua center';
				}
			}
			else 
			{
				if($diff <= 30)
				{
					$bg = 'bgyellow center';
				}
			}
			
		}
		return $bg;
    }

}
?>