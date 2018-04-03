<?php
class Qss_Bin_Trigger_OThanhToanHoaDon extends Qss_Lib_Trigger
{
	public function onUpdate($object)
	{
		parent::init();
		$this->checkPercent($object);
	}
	public function onInsert($object)
	{
		parent::init();
		$this->checkPercent($object);
	}
	
	private function checkPercent($object)
	{
		
	}
	
}
?>
