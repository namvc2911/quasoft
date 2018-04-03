
<?php
class Qss_Bin_Calculate_OZone_DonViTinh extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->OSanPham->DonViTinh(1);
	}
}
?>