<?php
class Qss_Bin_Calculate_OPhieuGiaoViec_GioBD extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
            if(Qss_Lib_System::fieldActive('OPhieuGiaoViec', 'GioBD'))
            {
                return $this->OCa->GioBatDau(1);
            }
	}
}
?>