<?php

class Qss_Service_Admin_User_Change extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		if($params['szNewPassWord'] != $params['szRePassWord'])
		{
			$this->setMessage($this->_translate(154,'Mật khẩu không giống nhau.'));
			$this->setError();
			return;
		}
		$user = new Qss_Model_Admin_User();
		$user->init(Qss_Register::get('userinfo')->user_id);
		$user->change($params);
	}

}
?>