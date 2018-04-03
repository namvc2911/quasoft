<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODanhSachCongViec_HanHoanThanh extends Qss_Lib_Style
{
	public function __doExecute()
	{
		$compare = Qss_Lib_Date::compareTwoDate($this->_data->HanHoanThanh, date('Y-m-d'));
		$diff = Qss_Lib_Date::diffTime($this->_data->HanHoanThanh, date('Y-m-d'),'D');
		$bg = '';
		if($this->_data->Status <= 2 && $this->_data->HanHoanThanh)
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
			elseif($diff == 2)
			{
				$bg = 'bgyellow center';
			}
		}
		return $bg;
	}

}
?>