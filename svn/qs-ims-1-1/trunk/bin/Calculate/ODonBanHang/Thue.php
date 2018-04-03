
<?php
class Qss_Bin_Calculate_ODonBanHang_Thue extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$arr = $this->OThueDonHang(0);
		foreach ($arr as $item)
		{
			$retval += $item->SoTienThue;
		}
		return $retval/1000;
	}
}
?>