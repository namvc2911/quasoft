
<?php
class Qss_Bin_Calculate_OThueThuNhapCaNhan_MucPhaiDongThue extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->MucToiThieu(1) + $this->MucGiamTru(1) + $this->TuThien(1);
	}
}
?>