<?php
class Qss_Bin_Trigger_ODonMuaHang extends Qss_Lib_Trigger
{
	/**
	 * Ngay nhan hang phai lon hon hon ngay dat hang
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->checkDateRange($object);
	}
	/**
	 * Ngay nhan hang phai lon hon hon ngay dat hang
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->checkDateRange($object);
	}
	
	/**
	 * Ngay nhan hang phai lon hon hon ngay dat hang
	 */
	private function checkDateRange($object)
	{
		$orderDate   = Qss_Lib_Date::i_fString2Time($object->getFieldByCode('NgayDatHang')->getValue());
		$receiveDate = Qss_Lib_Date::i_fString2Time($object->getFieldByCode('NgayYCNH')->getValue());
			
		if($orderDate > $receiveDate)
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
	}
}