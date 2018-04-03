<?php
class Qss_Bin_Calculate_OCTBangLuong_SoTienPhuCap extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ODanhSachNhanVien->OHopDongLaoDong->NhomPhuCap->TongTienPhuCap(1);
	}
}
?>