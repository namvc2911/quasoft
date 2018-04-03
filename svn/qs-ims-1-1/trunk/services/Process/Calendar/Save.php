<?php

class Qss_Service_Process_Calendar_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$process = new Qss_Model_Process();
		$process->saveCalendar($params);
	}

}
?>