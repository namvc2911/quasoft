
<?php
class Qss_Bin_Calculate_ONhanCongSX_SoGioCong extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ONhanCongSP->SoGioLamViec(1);
	}
}
?>