<?php

class Qss_Service_System_Calendar_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($fid)
	{
		$model = new Qss_Model_Calendar_Form();
		$model->delete($fid);
	}

}
?>