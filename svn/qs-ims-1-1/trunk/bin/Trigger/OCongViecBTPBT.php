<?php

class Qss_Bin_Trigger_OCongViecBTPBT extends Qss_Lib_Trigger
{

	public $_common;
	public $_maintain;

	public function __construct($form)
	{
		parent::__construct($form);
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_maintain = new Qss_Model_Extra_Maintenance();

	}

	/**
	 * onInsert:
	 * - cap nhat chi phi nhan cong theo cong viec bao tri
	 */
	public function onInserted($object)
	{
		parent::init();
	}

	/**
	 * onUpdate:
	 * - cap nhat chi phi nhan cong theo cong viec bao tri
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->checkDateInTimeOfWorkOrder($object);

	}
	
	public function onInsert($object) 
	{
		parent::init();
		$this->checkDateInTimeOfWorkOrder($object);
	}
	
	private function checkDateInTimeOfWorkOrder(Qss_Model_Object $object)
	{
		$ngayCongViec = $object->getFieldByCode('Ngay')->getValue();
		if(!$ngayCongViec)
		{
			return;
		}
		if($this->_params->Ngay)
		{
			$inRange = Qss_Lib_Date::checkInRangeTime(
				$ngayCongViec
				, $this->_params->NgayBatDau
				, $this->_params->Ngay);
		}
		else
		{
			$inRange = Qss_Lib_Date::compareTwoDate($ngayCongViec, $this->_params->NgayBatDau);
			$inRange = ($inRange >= 0)?true:false;
		}
		
		if(!$inRange)
		{
			//$this->setError();
			$this->setMessage('Ngày của công việc không nằm trong khoảng ngày của phiếu bảo trì.');	
		}
	}

}
