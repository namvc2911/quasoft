<?php

class Qss_Service_Object_Import extends Qss_Service_Abstract
{

	public function __doExecute (Qss_Model_Form $form, Qss_Model_Object $object, $arrIOID, $arrFID)
	{
		$user = Qss_Register::get('userinfo');
		$rights = $form->i_fGetRights($user->user_group_list);
		if ( ($rights & 4) )
		{
			foreach ($arrIOID as $key=>$val)
			{
				if($val)
				{
					if ( $object->b_Main )
					{
						$object->i_IFID = 0;
						$object->doInsertForm();
					}
					if ( $object->i_IFID )
					{
						$object->v_fUpdateRecform($val,$arrFID[$key]);
						$form = new Qss_Model_Form();
						$form->initData($object->i_IFID, $object->intDepartmentID);
						$mainObject = $form->o_fGetMainObject();
						$mainObject->initData($object->i_IFID, $object->intDepartmentID, $mainObject->i_IOID);
						if($this->setFieldCalculate($mainObject))
						{
							$mainObject->b_fSave();
						}
					}
				}
			}
		}
		else
		{
			$this->setMessage($this->_translate(146));
			$this->setError();
		}
	}
	public function setFieldCalculate(&$object)
	{
		$ret = false;
		foreach ($object->loadFields() as $key => $f)
		{
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if ( $f->bReadOnly && class_exists($classname))
			{
				$autocal = new $classname($object);
				$f->v_fSetValue($autocal->__doExecute());
				$ret = true;
			}
		}
		return $ret;
	}
}
?>