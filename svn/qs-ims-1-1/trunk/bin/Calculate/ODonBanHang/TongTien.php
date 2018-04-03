
<?php
class Qss_Bin_Calculate_ODonBanHang_TongTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->Thue(1) + $this->GiaTri(1);
	}
}
?>