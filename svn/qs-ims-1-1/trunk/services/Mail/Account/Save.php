<?php

class Qss_Service_Mail_Account_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$event = new Qss_Model_Mail();
		if($params['password'] != $params['repassword'])
		{
			$this->setMessage($this->_translate(154));
			$this->setError();
			return;
		}
		$event->saveAccount($params);
	}

}
?>