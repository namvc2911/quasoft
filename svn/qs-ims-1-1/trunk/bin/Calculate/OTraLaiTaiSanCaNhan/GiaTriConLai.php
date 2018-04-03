<?php
class Qss_Bin_Calculate_OTraLaiTaiSanCaNhan_GiaTriConLai extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$khauhao = Qss_Lib_Date::divDate($this->_object->getFieldByCode('Ngay')->getValue(),$this->OTaiSanCaNhan->NgayGiao(1));
		$khauhao = round($khauhao/30,2);
		$giatriconlai = $this->OTaiSanCaNhan->GiaTriConLai(1);
		$soluongbangiao = $this->OTaiSanCaNhan->SoLuong(1);
		$khauhaobangiao = $this->OTaiSanCaNhan->KhauHaoConLai(1);
		$soluongtralai = $this->_object->getFieldByCode('SoLuong')->getValue();
		return $giatriconlai - round((($giatriconlai/$soluongbangiao)/$khauhaobangiao)*$soluongtralai*$khauhao);
	}
}
?>