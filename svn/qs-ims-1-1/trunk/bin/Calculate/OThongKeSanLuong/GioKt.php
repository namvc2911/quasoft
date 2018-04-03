<?php
class Qss_Bin_Calculate_OThongKeSanLuong_GioKT extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
            if(Qss_Lib_System::fieldActive('OThongKeSanLuong', 'GioKT'))
            {
                return $this->OCa->GioKetThuc(1);
            }
	}
}
?>