<?php

class Qss_Service_Object_Updown extends Qss_Service_Abstract
{

	public function __doExecute ($ifid, $deptid,$objid,$ioid,$type)
	{
		$form = new Qss_Model_Form();
		$user = Qss_Register::get('userinfo');
		if ( $form->initData($ifid, $deptid, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			$object = $form->o_fGetObjectByCode($objid);
			$rights = $rights & ($object->intRights | 48) & (($rights & 20)?63:0);
			if ((($ioid && $object->bEditing && $rights & 4) || (!$ioid && $object->bInsert && $rights & 1)))
			{
				$object->initData($ifid, $deptid, $ioid);
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