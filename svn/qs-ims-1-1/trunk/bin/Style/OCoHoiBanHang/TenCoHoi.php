<?php
class Qss_Bin_Style_OCoHoiBanHang_TenCoHoi extends Qss_Lib_Style
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$bg = '';
		if($this->_data->DamPhan == 1)
		{
			$bg = 'bold';
		}
		return $bg;
	}
}
?>