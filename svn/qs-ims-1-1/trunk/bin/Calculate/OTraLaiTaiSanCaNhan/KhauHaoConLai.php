<?php
class Qss_Bin_Calculate_OTraLaiTaiSanCaNhan_KhauHaoConLai extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$khauhao = Qss_Lib_Date::divDate($this->_object->getFieldByCode('Ngay')->getValue(),$this->OTaiSanCaNhan->NgayGiao(1));
		return $this->OTaiSanCaNhan->KhauHaoConLai(1) - round($khauhao/30,2);
	}
}
?>