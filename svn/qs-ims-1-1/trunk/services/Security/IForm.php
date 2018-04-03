<?php
/**
 * This can check user right, you can use in xml config or in the base controller
 * 
 * @author HuyBD
 *
 */
class Qss_Service_Security_IForm extends Qss_Service_Abstract
{
	public function __doExecute (Qss_Model_UserInfo $user, $ifid, $rights)
	{
		$acrights = $user->checkRightsOnIForm($fid);
		if($acrights & $rights)
		{
			return true;
		}
		$this->setError();
		$this->setMessage('Bạn không có quyền thực hiện thao tác này');
		return false;
	}
}