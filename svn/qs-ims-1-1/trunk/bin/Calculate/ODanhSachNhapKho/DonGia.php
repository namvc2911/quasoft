<?php
class Qss_Bin_Calculate_ODanhSachNhapKho_DonGia extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$ngayChungTu = $this->NgayChungTu(1)?$this->NgayChungTu(1):date('d-m-Y');
		$masp        = $this->MaSanPham(1)?$this->MaSanPham(1):'';
		
		
		$sql = sprintf('
			select ds.DonGia from ONhapKho as nk
			inner join OLoaiNhapKho as lnk
			inner join ODanhSachNhapKho as ds on nk.`IFID_M402` = ds.`IFID_M402`
			where nk.`NgayChungTu` <= %1$s 
			and ds.`MaSanPham` = %2$s
			/*and lnk.`Loai` = \'MUAHANG\'*/
			order by nk.`NgayChungTu` DESC, nk.IOID DESC, ds.IOID DESC
			Limit 1
			', $this->_db->quote(Qss_Lib_Date::displaytomysql($ngayChungTu))
			, $this->_db->quote($masp));
		$dataSql = $this->_db->fetchOne($sql);
		
		//echo $dataSql?Qss_Lib_Util::formatMoney($dataSql->DonGia):$this->OSanPham->GiaMua(1); die;
		return $dataSql?Qss_Lib_Util::formatMoney($dataSql->DonGia):$this->OSanPham->GiaMua(1); // GiaMua
	}
}
?>