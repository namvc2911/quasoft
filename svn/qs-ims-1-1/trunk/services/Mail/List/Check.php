<?php

class Qss_Service_Mail_List_Check extends Qss_Service_Abstract
{

	public function __doExecute ($uid,$mlid,$rights)
	{
		if(!$mlid)
		{
			return true;
		}
		$event = new Qss_Model_Mail();
		$retval = $event->getCheckListRights($uid,$mlid);
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