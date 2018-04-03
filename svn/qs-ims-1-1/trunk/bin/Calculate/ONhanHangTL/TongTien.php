
<?php
class Qss_Bin_Calculate_ONhanHangTL_TongTien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$tax = Qss_Lib_System::fieldActive('ONhanHangTL','Thue')?$this->Thue(1):0; 
		return $tax + $this->GiaTriDonHang(1);
	}
}
?>