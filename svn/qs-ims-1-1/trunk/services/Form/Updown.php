<?php

class Qss_Service_Form_Updown extends Qss_Service_Abstract
{

	public function __doExecute ($i_IFID, $i_DeptID,$type)
	{
		$form = new Qss_Model_Form();
		$user = Qss_Register::get('userinfo');
		if ( $form->initData($i_IFID, $i_DeptID, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 4) )
			{
				$object = $form->o_fGetMainObject();
				$object->initData($form->i_IFID, $form->i_DepartmentID, 0);
				$object->doUpDown($type);
				$form->updateReader(Qss_Register::get('userinfo')->user_id);
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
		else
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
		}
	}

}