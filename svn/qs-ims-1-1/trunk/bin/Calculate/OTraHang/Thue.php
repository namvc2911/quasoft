
<?php
class Qss_Bin_Calculate_OTraHang_Thue extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		if(Qss_Lib_System::objectInForm('M403','OThueDonHang'))
		{
			$retval = 0;
			$arr = $this->OThueDonHang(0);
			foreach ($arr as $item)
			{
				$retval += $item->SoTienThue;
			}
			return $retval/1000;
		}
		else 
		{
			return 0;
		}
	}
}
?>