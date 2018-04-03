<?php
class Qss_Bin_Trigger_OKeHoachNhanHang extends Qss_Lib_Trigger
{
	/**
	 * Ngay nhan hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->checkDateAvailable($object);
	}
	/**
	 * Ngay nhan hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->checkDateAvailable($object);
	}
	
	/**
	 * Ngay nhan hang phai trong khoang tu ngay dat hang den ngay yeu cau
	 */
	private function checkDateAvailable($object)
	{
		if($object->FormCode == 'M401')
		{
			$orderDateAlias    = 'NgayDatHang';
			$requiredDateAlias = 'NgayYCNH';
			$receiveDateAlias  = 'NgayNhanHang';
		}
		elseif($object->FormCode == 'M507')
		{
			$orderDateAlias    = 'NgayYeuCau';
			$requiredDateAlias = 'NgayNhan';
			$receiveDateAlias  = 'NgayNhanHang';			
		}
		
		$orderDate    = Qss_Lib_Date::i_fMysql2Time($this->_params->$orderDateAlias);// Ngay dat hang kieu so co the so sanh
		$requiredDate = Qss_Lib_Date::i_fMysql2Time($this->_params->$requiredDateAlias);// Ngay yeu cau kieu so co the so sanh
		$receiveDate  = Qss_Lib_Date::i_fString2Time($object->getFieldByCode($receiveDateAlias)->getValue()); // Ngay nhan hang kieu so co the so sanh
		
		// kiem tra ngay dat hang co trong khoang tu ngay dat hang den ngay yeu cau
		// loc toan bo ko break
		if( $receiveDate < $orderDate || $receiveDate > $requiredDate )
		{
			$this->setMessage($this->_translate(1));
			$this->setError();
		}
	}
}