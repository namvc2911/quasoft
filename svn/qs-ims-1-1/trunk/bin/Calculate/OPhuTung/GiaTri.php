
<?php
class Qss_Bin_Calculate_OPhuTung_GiaTri extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ThayThe(1)?$this->ODanhSachPhuTung->OSanPham->GiaMua(1):0;
	}
}
?>