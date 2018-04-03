<?php
class Qss_Bin_Trigger_OLichThietBi extends Qss_Lib_Trigger
{
	public function onInserted($object)
	{
		//send email if breakdown
		parent::init();
	}
}