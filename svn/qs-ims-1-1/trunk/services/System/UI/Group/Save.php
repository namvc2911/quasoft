<?php

class Qss_Service_System_UI_Group_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model  = new Qss_Model_System_UI();
		$model->saveGroup($params);
	}

}
?>