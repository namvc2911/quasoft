<?php

class Qss_Service_Admin_Print_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$design = new Qss_Model_Admin_Print();
		$design->save($params);
	}

}
?>