<?php
class Qss_Bin_Calculate_OThongKeSanLuong_GioBD extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
            if(Qss_Lib_System::fieldActive('OThongKeSanLuong', 'GioBD'))
            {
                return $this->OCa->GioBatDau(1);
            }
	}
}
?>