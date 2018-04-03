<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Object_Refresh extends Qss_Lib_Service
{

	public function __doExecute ($a_Data)
	{
		/* The validation should be here. This will add to message property that
		 * we can access in caller e.g: $this->services->form->save->message */
		$form = new Qss_Model_Form();
		$ifid = (int) $a_Data['ifid'];
		$deptid = (int) $a_Data['deptid'];
		$ioid = (int) $a_Data['ioid'];
		$objid = $a_Data['objid'];
		$user = Qss_Register::get('userinfo');
		if ( $ifid && $deptid && $form->initData($ifid, $deptid) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			$object = $form->o_fGetObjectByCode($objid);
			if ( $rights & 15)
			{
				$object->i_UserID = Qss_Register::get('userinfo')->user_id;
				if ( $object->initData($ifid, $deptid, $ioid) )
				{
					if ( $object && $this->b_fValidate($object, $a_Data) )
					{
						//$object->b_fSave();
						$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
						if(!class_exists($classname))
						{
							$classname = 'Qss_Lib_Onload';
						}
						$onload = new $classname(null,$object);
						$onload->__doExecute();
						$fields = $object->loadFields();
						foreach ($fields as $key => $field)
						{
							
							$onload->{$field->FieldCode}();
							//check lại nếu có lọc
							if(count($field->arrFilters) 
								&& !$field->checkLookup())
							{
								
								$field->setRefIOID(0);
								$field->setValue('');
							}
							else if ( $field->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
							{
								$object->setRefValue($field);
							}
						}
						$object->autoCalculate();//@todo: Để đây đúng ko ta
					}
					else
					{
						$this->setError();
					}
				}
				else
				{
					$this->setMessage($this->_translate(146));
					$this->setError();
				}
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
		return $object;
	}

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */


}
?>