<?php

class Qss_Service_Event_Check extends Qss_Service_Abstract
{

	public function __doExecute ($uid,$eventid,$rights)
	{
		if(!$eventid)
		{
			return true;
		}
		$event = new Qss_Model_Event();
		$retval = $event->getCheckRights($uid,$eventid);
		if($retval > 0 && $retval <= $rights)
		{
			return true;
		}
		else 
		{
			$this->setError();
			$this->setMessage('Bạn không có quyền thực hiện thao tác này');
			return false;
		}
	}

}
?>