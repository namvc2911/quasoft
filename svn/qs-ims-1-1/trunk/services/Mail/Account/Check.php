<?php

class Qss_Service_Mail_Account_Check extends Qss_Service_Abstract
{

	public function __doExecute ($uid,$maid,$rights)
	{
		if(!$maid)
		{
			return true;
		}
		$event = new Qss_Model_Mail();
		$retval = $event->getCheckRights($uid,$maid);
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