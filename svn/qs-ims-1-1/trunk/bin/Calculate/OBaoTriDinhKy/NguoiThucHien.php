<?php
class Qss_Bin_Calculate_OBaoTriDinhKy_NguoiThucHien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		//$mathietbi = $this->_object->getFieldByCode('MaThietBi')->getRef
		//return $this->ODanhSachThietBi->ODanhSachNhanVien->TenNhanVien(1);
		$matb = $this->_object->getFieldByCode('MaThietBi')->getRefIOID();
		$sql = sprintf('select * from ODanhSachThietBi where IOID = %1$d',$matb);
		$dataSQL = $this->_db->fetchOne($sql);
		if($dataSQL)
		{
			return (int) $dataSQL->Ref_QuanLy;			
		}	
	}
}
?>
