<?php
class Qss_Bin_Trigger_OKeHoachSanPham extends Qss_Lib_Product_Trigger
{
	/**
	 * - Update end date
	 * - Check attributes conditions: required or changed!
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->updateEndDateInMainObject($object);
	}
	
	/**
	 * - Update end date
	 * - Check attributes conditions: required or changed!
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->updateEndDateInMainObject($object);
	}
	
	/**
	 * - Check attributes when item code change value
	 */
	public function onDeleted($object)
	{
		parent::init();
		$this->updateEndDateInMainObject($object);
	}
	
	
	private function updateEndDateInMainObject(Qss_Model_Object $object)
	{
		$extra       = new Qss_Model_Extra_Extra();
		// Lay ngay cuoi cung de cap nhat lai vao doi tuong chinh xu ly don hang
		$lastestDate = Qss_Lib_Date::mysqltodisplay(@(string)$extra->getTable(array('*'), 'OKeHoachSanPham', array('IFID_M901'=>$this->_params->IFID_M901, 'Level'=>1), array('NgayKetThuc DESC'), 1, 1)->NgayKetThuc);
		$insert      = array();
		$insert['OKeHoachCungUng'][0]['NgayKetThuc'] = $lastestDate;
		
		$service = $this->services->Form->Manual('M901' , $object->i_IFID, $insert, false);
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}	
	}	
}