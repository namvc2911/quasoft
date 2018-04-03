<?php

class Qss_Service_Field_Picture_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($ifid, $fieldid, $id,$ext)
	{
		$form = new Qss_Model_Form();
		$user = Qss_Register::get('userinfo');
		if ( $form->initData($ifid, 1, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			$field = $form->o_fGetFieldByID($fieldid);
			if ( ($rights & 4) && $field && !$field->bReadOnly)
			{
				$field->delete($id,$ext);
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
	}

}