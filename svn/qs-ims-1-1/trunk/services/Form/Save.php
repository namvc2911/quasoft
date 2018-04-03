<?php
/**
 * Save form service
 *
 * @author HuyBD
 *
 */
class Qss_Service_Form_Save extends Qss_Lib_Service
{

	public function __doExecute ($a_Data)
	{
		/* The validation should be here. This will add to message property that
		 * we can access in caller e.g: $this->services->form->save->message */

		$retval = 0;
		$form = new Qss_Model_Form();
		$fid = $a_Data['fid'];
		$ifid = $a_Data['ifid'];
		$deptid = $a_Data['deptid'];
		$user = Qss_Register::get('userinfo');
		$upper = 0;//thằng cha
		if ( $ifid && $deptid && $form->initData($ifid, $deptid) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 4) )
			{
				$objects = $form->o_fGetMainObjects();
				foreach ($objects as $object)
				{
					$fields = $object->loadFields();
					$object->initData($form->i_IFID, $form->i_DepartmentID, $object->i_IOID);
					if ( !$object || !$this->b_fValidate($object, $a_Data) )
					{
						$this->setError();
					}
					$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
					if(!class_exists($classname))
					{
						$classname = 'Qss_Lib_Onload';
					}
					$onload = new $classname($form,$object);
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
					if(!$this->b_fCheckRequire($object))
					{
						$this->setError();
					}
				}
				if(!$this->isError())
				{
					foreach ($objects as $object)
					{
						$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
						if(class_exists($classname))
						{
							$trigger = new $classname($form);
							$trigger->onUpdate($object);
							$this->setMessage($trigger->getMessage());
							if($trigger->isError())
							{
								$this->setError();
								$this->setStatus($trigger->getStatus());
							}
						}
						if(!$this->isError())
						{
							if($object->b_fSave($upper))
							{
								$retval = $object->i_IFID;
								$form->i_IFID = $retval;
								$form->updateReader(Qss_Register::get('userinfo')->user_id);
								if(class_exists($classname))
								{
									$trigger = new $classname($form);
									$trigger->onUpdated($object);
									$this->setMessage($trigger->getMessage());
								}
								//gửi mail nếu có class
								$classname = 'Qss_Bin_Notify_Mail_'.$form->FormCode.'_Updated';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
								//gửi sms nếu có class
								$classname = 'Qss_Bin_Notify_Sms_'.$form->FormCode.'_Updated';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
								//chạy validate nếu có class
								$classname = 'Qss_Bin_Notify_Validate_'.$form->FormCode.'_Updated';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
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
		elseif ( $form->init($fid, $deptid, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 1) )
			{
				$objects = $form->o_fGetMainObjects();
				foreach ($objects as $object)
				{
					$fields = $object->loadFields();
					if ( !$object || !$this->b_fValidate($object, $a_Data) )
					{
						$this->setError();
					}
					$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
					if(!class_exists($classname))
					{
						$classname = 'Qss_Lib_Onload';
					}
					$onload = new $classname($form,$object);
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
					}
					
					if(!$this->b_fCheckRequire($object))
					{
						$this->setError();
					}
				}
				if(!$this->isError())
				{
					foreach ($objects as $object)
					{
						if($form->i_IFID)
						{
							$object->i_IFID = $form->i_IFID;
						}
						$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
						if(class_exists($classname))
						{
							$trigger = new $classname($form);
							$trigger->onInsert($object);
							$this->setMessage($trigger->getMessage());
							if($trigger->isError())
							{
								$this->setError();
								$this->setStatus($trigger->getStatus());
							}
						}
						if(!$this->isError())
						{
							if($object->b_fSave())
							{
								$retval = $object->i_IFID;
								$form->i_IFID = $retval;
								Qss_Cookie::set('form_selected', $retval);
								$form->updateReader(Qss_Register::get('userinfo')->user_id);
								//inserted
								if(class_exists($classname))
								{
									$trigger = new $classname($form);
									$trigger->onInserted($object);
									$this->setMessage($trigger->getMessage());
								}
								//gửi mail nếu có class
								$classname = 'Qss_Bin_Notify_Mail_'.$form->FormCode.'_Inserted';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
								//gửi sms nếu có class
								$classname = 'Qss_Bin_Notify_Sms_'.$form->FormCode.'_Inserted';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
								//gửi validate nếu có class
								$classname = 'Qss_Bin_Notify_Validate_'.$form->FormCode.'_Inserted';
								if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
								{
									$trigger = new $classname($form);
									$trigger->init();
									$trigger->__doExecute();
								}
							}
						}
						else
						{
							break;
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
		else
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
		}
		if(!$this->isError())
		{
			$bashmodel = new Qss_Model_Bash();
			$bash = $bashmodel->getByFormAndStep($fid,0);
			foreach ($bash as $item)
			{
				$service = new Qss_Service();
				$service->Bash->Execute($form,$item->BID);
			}
		}
		if(!$this->isError())
		{
			$this->setStatus($retval);
			$service = new Qss_Service();
			$validation = $service->Form->Validate($form);
			if($validation->isError())
			{
				$this->setMessage($validation->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
		return $retval;
	}
}
?>