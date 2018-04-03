<?php

class Qss_Service_Event_Action extends Qss_Service_Abstract
{

	public function __doExecute ($id,$action)
	{
		$event = new Qss_Model_Event();
		$event->action($id,$action);
	}

}
?>