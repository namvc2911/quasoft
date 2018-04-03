<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Object_Save extends Qss_Lib_Service
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
		$retval = 0;
		$upper = 0;
		if ( $ifid && $deptid && $form->initData($ifid, $deptid) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			$object = $form->o_fGetObjectByCode($objid);
			$rights = $rights & ($object->intRights | 48) & (($rights & 20)?63:0);
			//chỉ cần form cho sửa và object thì phụ thuộc hoàn toàn vào quyền cơ bản
			if (($rights & 4) && (($ioid && $object->bEditing && $object->intRights & 4) || (!$ioid && $object->bInsert && $object->intRights & 1)))
			{
				$object->i_UserID = Qss_Register::get('userinfo')->user_id;
				if ( $object->initData($ifid, $deptid, $ioid) )
				{
					$fields = $object->loadFields();
					if ( $object && $this->b_fValidate($object, $a_Data))
					{
						$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
						if(!class_exists($classname))
						{
							$classname = 'Qss_Lib_Onload';
						}
						$onload = new $classname(null,$object);
						$onload->__doExecute();
						//check if lookup is ok
						foreach ($fields as $field)
						{
							if($field->RefFieldCode
								&& $field->intInputType == 3
								&& $field->intInputType == 4)
							{
								$onload->{$field->FieldCode}();
								if(count($field->arrFilters) && !$field->checkLookup())
								{
									$field->setRefIOID(0);
									$field->setValue('');
								}
								else if ( $field->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
								{
									$object->setRefValue($field);
								}
							}
							if($field->bEffect && $field->ObjectCode == $field->RefObjectCode)
							{
								$upper = @$a_Data[$field->ObjectCode.'_'.$field->FieldCode.'_order'];
							}
						}
						if($this->b_fCheckRequire($object))
						{
							/*if($ioid && $user->user_id != $object->i_UserID)
							{
								$this->setMessage('Bạn không được sửa bản ghi của người khác.');
								$this->setError();
								return;
							}*/
							$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
							if(class_exists($classname))
							{
								$trigger = new $classname($form);
								if($ioid)
								{
									$trigger->onUpdate($object);
								}
								else
								{
									$trigger->onInsert($object);
								}
								$this->setMessage($trigger->getMessage());
								if($trigger->isError())
								{
									$this->setError();
									$this->setStatus($trigger->getStatus());
								}
							}
							if(!$this->isError() && $object->b_fSave($upper))
							{
								$retval = $object->i_IOID;
								//$this->setStatus($object->i_IOID);
								if(class_exists($classname))
								{
									$trigger = new $classname($form);
									if($ioid)
									{
										$trigger->onUpdated($object);
									}
									else
									{
										$trigger->onInserted($object);
									}
									$this->setMessage($trigger->getMessage());
									$this->setStatus($trigger->getStatus());
								}
								$mainObject = $form->o_fGetMainObject();
								$mainObject->initData($ifid, $deptid, $mainObject->i_IOID);
								if($this->setFieldCalculate($mainObject,false))
								{
									$mainObject->b_fSave();
								}
								if(!$ioid)
								{
									Qss_Cookie::set('object_selected', $object->i_IOID);
								}
							}
						}
						else
						{
							$this->setError();
						}
					}
					else 
					{
						$this->setError();
					}
				}
				else
				{
					$this->setMessage($this->_translate(145));
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
		if(!$this->isError())
		{
			$this->setStatus($object->i_IOID);
		}
		return $retval;
	}
}
?>