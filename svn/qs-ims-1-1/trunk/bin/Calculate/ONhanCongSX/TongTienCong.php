
<?php
class Qss_Bin_Calculate_ONhanCongSX_TongTienCong extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->SoGioLam(1) * $this->TienCongTrenGio(1);
	}
}
?>