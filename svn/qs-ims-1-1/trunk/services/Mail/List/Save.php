<?php

class Qss_Service_Mail_List_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$event = new Qss_Model_Mail();
		$event->saveList($params);
	}

}
?>