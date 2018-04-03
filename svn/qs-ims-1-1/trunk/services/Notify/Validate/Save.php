<?php

class Qss_Service_Notify_Validate_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$notify = new Qss_Model_Notify_Validate();
		$notify->save($params);
	}

}
?>

