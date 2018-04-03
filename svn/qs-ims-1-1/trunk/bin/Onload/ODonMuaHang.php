<?php
class Qss_Bin_Onload_ODonMuaHang extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		 parent::__doExecute();
		 $loaitien = $this->_object->getFieldByCode('LoaiTien')->getValue();
		 if($loaitien)
		 {
		 	$this->_object->getFieldByCode('ChiPhiVanChuyen')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('GiamTru')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('PhatSinhTang')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('GiaTriDonHang')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('Thue')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('ChungChi')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('TongTien')->setRefIOID($loaitien);
		 	$this->_object->getFieldByCode('TongTienDH')->setRefIOID($loaitien);
		 }
	}
	
}