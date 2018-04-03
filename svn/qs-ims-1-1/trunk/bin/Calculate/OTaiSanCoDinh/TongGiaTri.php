
<?php
class Qss_Bin_Calculate_OTaiSanCoDinh_TongGiaTri extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanPham->GiaMua(1);
	}
}
?>