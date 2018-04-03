<?php
class Qss_Bin_Calculate_OTaiKhoanQuanTri_TenTaiKhoan extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		/*
		 * $parent = '{this:TaiKhoanCha_261}'; 
		 * $retval = '{this:TaiKhoan_258}'; 
		 * if($parent != '') { $retval = $parent . ' / ' . $retval; }
		 */
		$parent = $this->TaiKhoanCha(1); 
		$retval = $this->TaiKhoan(1); 
		$retval = ($parent != '')?$parent . ' / ' . $retval:$retval; 
		return $retval;
	}
}
?>