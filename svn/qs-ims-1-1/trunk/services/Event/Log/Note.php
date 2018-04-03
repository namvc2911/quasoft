<?php

class Qss_Service_Event_Log_Note extends Qss_Service_Abstract
{

	public function __doExecute ($elid,$note)
	{
		$event = new Qss_Model_Event();
		$event->saveNote($elid,$note);
	}

}
?>