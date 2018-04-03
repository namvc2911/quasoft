
<?php
class Qss_Bin_Calculate_OPhieuBaoTri_TongThoiGian extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->ThoiGianCho(1) + $this->ThoiGianKham(1) + $this->ThoiGianTimPhuTung(1) + $this->ThoiGianXuLy(1) + $this->ThoiGianKhoiDongLai(1);
	}
}
?>