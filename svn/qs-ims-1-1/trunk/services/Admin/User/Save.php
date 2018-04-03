<?php

class Qss_Service_Admin_User_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$user = new Qss_Model_Admin_User();
		$userid = (int) $params['userid'];
		if(!$userid && !$params['szPassWord'])
		{
			$this->setMessage($this->_translate(151,'Mật khẩu yêu cầu bắt buộc.'));
			$this->setError();
			return;
		}
		if(!$userid && $user->isDuplicate($params['szUserID']))
		{
			$this->setMessage($this->_translate(158,'Tên truy cập đã tồn tại.'));
			$this->setError();
			return;
		}
		$user->init($userid);
		if(!$user->save($params))
		{
			$this->setMessage($this->_translate(1));
			$this->setError();
		}
	}

}
?>