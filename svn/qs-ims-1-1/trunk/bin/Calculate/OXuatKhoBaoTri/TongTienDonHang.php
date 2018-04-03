<?php
class Qss_Bin_Calculate_OXuatKhoBaoTri_TongTienDonHang extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = 0;
		$arr = $this->ODanhSachXuatKho(0);
		foreach ($arr as $item)
		{
			$retval += $item->ThanhTien;
		}
		return $retval/1000;
	}
}
?>