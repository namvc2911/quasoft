
<?php
class Qss_Bin_Calculate_ODonMuaHang_TongTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$tax = (Qss_Lib_System::fieldActive('ODonMuaHang','Thue') && is_numeric($this->Thue(1)))?$this->Thue(1):0; 
		return $tax + ($this->GiaTriDonHang(1) - $this->GiamTru(1));
	}
}
?>