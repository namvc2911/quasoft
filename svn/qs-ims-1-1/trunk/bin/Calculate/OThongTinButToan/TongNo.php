
<?php
class Qss_Bin_Calculate_OThongTinButToan_TongNo extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$arr = $this->OButToan(0);
		foreach ($arr as $item)
		{
			$retval += $item->BenNo;
		}
		return $retval/1000;
	}
}
?>