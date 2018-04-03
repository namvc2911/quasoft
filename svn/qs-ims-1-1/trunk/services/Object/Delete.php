<?php

class Qss_Service_Object_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($i_IFID, $i_FDeptID, $i_IOID, $i_ObjID)
	{
		$form = new Qss_Model_Form();
		$user = Qss_Register::get('userinfo');
		$rowdeleted = 0;
		for($i=0; $i < count($i_IOID);$i++)
		{
			$bDeleted = true;
			$ioid = $i_IOID[$i];
			if ( $form->initData($i_IFID, $i_FDeptID, $user->user_id) )
			{
				$rights = $form->i_fGetRights($user->user_group_list);
				$rights = $rights & 4;
				$object = $form->o_fGetObjectByCode($i_ObjID);
				$rights = ($rights?8:0) & $object->intRights;
				if ( ($rights & 8) )
				{
					//$object = new Qss_Model_Object();
					//$object->v_fInit($i_ObjID, $form->FormCode);
					//$object->i_UserID = Qss_Register::get('userinfo')->user_id;
					if($object && $object->bDeleting)
					{
						if ( $object->initData($i_IFID, $i_FDeptID, $ioid) )
						{
							$arrCrossLinks = $object->getCrossLinks();
							if(!count( (array) $arrCrossLinks))
							{
								$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
								if(class_exists($classname))
								{
									$trigger = new $classname($form);
									$trigger->onDelete($object);
									$this->setMessage($trigger->getMessage());
									if($trigger->isError())
									{
										$this->setError();
									}
								}
								if(!$this->isError())
								{
									$object->b_fDelete();
									$mainObject = $form->o_fGetMainObject();
									$mainObject->initData($i_IFID, $i_FDeptID, $object->i_IOID);
									if($this->setFieldCalculate($mainObject,false))
									{
										$mainObject->b_fSave();
									}
									if(class_exists($classname))
									{
										$trigger = new $classname($form);
										$trigger->onDeleted($object);
										$this->setMessage($trigger->getMessage());
									}
								}
							}
							else
							{
								$bDeleted = false;
								$this->setError();
								$this->setMessage('Dữ liệu đã được dùng trong các mô đun sau:');
								foreach($arrCrossLinks as $item)
								{
									$this->setMessage($item->Name);
								}
							}
						}
						else
						{
							$bDeleted = false;
							$this->setMessage($this->_translate(145));
							$this->setError();
						}
					}
					else
					{
						$bDeleted = false;
						$this->setMessage($this->_translate(146));
						$this->setError();
					}
				}
				else
				{
					$bDeleted = false;
					$this->setMessage($this->_translate(146));
					$this->setError();
				}
			}
			else
			{
				$bDeleted = false;
				$this->setMessage($this->_translate(145));
				$this->setError();
			}
		}
		if($this->isError())
		{
			$this->setMessage($rowdeleted . '/'.count($i_IOID). ' ' .$this->_translate(2));
		}
	}
	public function setFieldCalculate(&$object)
	{
		$ret = false;
		foreach ($object->loadFields() as $key => $f)
		{
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if ( $f->szRegx == Qss_Lib_Const::FIELD_REG_RECALCULATE && class_exists($classname))
			{
				$autocal = new $classname($object);
				$f->setValue($autocal->__doExecute());
				$ret = true;
			}
		}
		return $ret;
	}
}
?>