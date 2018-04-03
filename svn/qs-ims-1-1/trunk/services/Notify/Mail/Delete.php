<?php

class Qss_Service_Notify_Mail_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$notify = new Qss_Model_Notify_Mail();
		$notify->deleteNotify($params['FID']);
	}

}
?>

