<?php

class Qss_Service_Notify_Sms_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$notify = new Qss_Model_Notify_Sms();
		$notify->save($params);
	}

}
?>

