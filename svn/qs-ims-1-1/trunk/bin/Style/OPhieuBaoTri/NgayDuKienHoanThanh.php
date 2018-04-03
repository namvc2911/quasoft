<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OPhieuBaoTri_NgayDuKienHoanThanh extends Qss_Lib_Style
{
    public function __doExecute()
    {
  	  	$compare = Qss_Lib_Date::compareTwoDate($this->_data->NgayDuKienHoanThanh, date('Y-m-d'));
		$diff = Qss_Lib_Date::diffTime($this->_data->NgayDuKienHoanThanh, date('Y-m-d'),'D');
		$bg = '';
		if($this->_data->Status <= 3 && $this->_data->NgayDuKienHoanThanh)
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
			
		}
		return $bg;
    }

}
?>