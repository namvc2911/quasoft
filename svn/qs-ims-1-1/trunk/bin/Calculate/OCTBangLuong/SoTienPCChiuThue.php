<?php
class Qss_Bin_Calculate_OCTBangLuong_SoTienPCChiuThue extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODanhSachNhanVien->OHopDongLaoDong->NhomPhuCap->TongTienPCChiuThue(1);
	}
}
?>