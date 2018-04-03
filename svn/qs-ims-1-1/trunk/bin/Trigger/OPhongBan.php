<?php
class Qss_Bin_Trigger_OPhongBan extends Qss_Lib_Trigger
{
	/**
	 * onInsert
	 * Ngày bắt đấu có lớn hơn ngày kết thúc không?
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->validateDate($object);
	}
	/**
	 * onUpdate
	 * Ngày bắt đấu có lớn hơn ngày kết thúc không?
	 */
    public function onUpdate($object)
	{
		parent::init();
		$this->validateDate($object);
	}
	
	private function validateDate($object)
	{
		$start      = $object->getFieldByCode('NgayBatDau')->getValue();
		$end        = $object->getFieldByCode('NgayKetThuc')->getValue();
		$microStart = strtotime($start);
		$microEnd   = strtotime($end);
		
		if($end && $end < $start)
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
	}
  
}
?>