
<?php
class Qss_Bin_Calculate_ONhanCongSX_TienCongTrenGio extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return  $this->ONhanCongSP->TienCongTrenGio(1);
	}
}
?>