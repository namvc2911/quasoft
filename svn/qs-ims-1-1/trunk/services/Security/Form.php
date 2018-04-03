<?php
/**
 * This can check user right, you can use in xml config or in the base controller
 * 
 * @author HuyBD
 *
 */
class Qss_Service_Security_Form extends Qss_Service_Abstract
{
	public function __doExecute (Qss_Model_UserInfo $user, $fid, $rights)
	{
		$form = new Qss_Model_Form();
		$form->init($fid,$user->user_dept_id,$user->user_id);
		$acrights = $form->i_fGetRights($user->user_group_list);
		if($acrights & $rights)
		{
			return true;
		}
		$this->setError();
		$this->setMessage('Bạn không có quyền thực hiện thao tác này');
		return false;
	}
}