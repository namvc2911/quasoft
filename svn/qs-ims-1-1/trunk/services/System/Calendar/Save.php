<?php

class Qss_Service_System_Calendar_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_Calendar_Form();
		$model->save($params);
	}

}
?>