<?php
class Qss_Bin_Calculate_ODanhSachNhapKho_ThanhTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
            $qty = $this->SoLuong(1)?$this->SoLuong(1):0;
            $price = $this->DonGia(1)?$this->DonGia(1):0;
            $cal = $qty * $price;            
            return round($cal,0);
	}
}
?>