<?php
/**
 * This can check user right, you can use in xml config or in the base controller
 *
 * @author HuyBD
 *
 */
class Qss_Service_Security_UserLogin extends Qss_Service_Abstract
{

	public function __doExecute ($userid, $pass, $dept,$level,$session_id = null)
	{
		$userinfo = new Qss_Model_UserInfo();
		$userinfo->user_name = $userid;
		$userinfo->user_password = $pass;
		$userinfo->user_dept_id = $dept;
		if($userinfo->user_mobile === null)
		{
			$check = new Qss_Lib_Mobile_Detect();
			$userinfo->user_mobile = $check->isTablet() || $check->isMobile();
		}
		$loginret = $userinfo->checkLogin($level,$session_id);
		$this->setStatus($loginret);
		return $userinfo;
	}

}