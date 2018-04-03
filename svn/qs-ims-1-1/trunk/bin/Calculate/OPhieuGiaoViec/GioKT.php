<?php
class Qss_Bin_Calculate_OPhieuGiaoViec_GioKT extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
            if(Qss_Lib_System::fieldActive('OPhieuGiaoViec', 'GioKT'))
            {
                return $this->OCa->GioKetThuc(1);
            }
	}
}
?>