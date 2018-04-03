<?php

class Qss_Service_Form_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($i_IFID, $i_DeptID)
	{
		$db = Qss_Db::getAdapter('main');
		$db->beginTransaction();
		$form = new Qss_Model_Form();
		$user = Qss_Register::get('userinfo');
		$rowdeleted = 0;
		for($i=0; $i < count($i_IFID);$i++)
		{
			$bDeleted = true;
			$ifid = $i_IFID[$i];
			$deptid = $i_DeptID[$i];
			if ( $form->initData($ifid, $deptid, $user->user_id) )
			{
				$rights = $form->i_fGetRights($user->user_group_list);
				if ( ($rights & 8) )
				{
					$classname = 'Qss_Bin_Validation_'.$form->FormCode;
					if(class_exists($classname))
					{
						$validation = new $classname($form);
						$validation->init();
						$validation->onDeleted();
						if($validation->isError())
						{
							$bDeleted = false;
							$this->setError();
							$this->setMessage($validation->getMessage());
						}
					}
					if(!$this->isError())
					{
						$arrCrossLinks = $form->getCrossLinks();
						if(count( (array) $arrCrossLinks))
						{
							$bDeleted = false;
							$this->setError();
							$this->setMessage($this->_translate(1));
							foreach($arrCrossLinks as $item)
							{
								$this->setMessage($item->Name);
							}
						}
					}
					if(!$this->isError())
					{
						$form->v_fTrace($user->user_id,'Deleted');//
						$form->b_fDelete();
						//$form->updateReader(Qss_Register::get('userinfo')->user_id);
					}
					if(!$this->isError())
					{
						//gửi mail nếu có class
						$classname = 'Qss_Bin_Notify_Mail_'.$form->FormCode.'_Deleted';
						if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
						{
							$trigger = new $classname($form);
							$trigger->init();
							$trigger->__doExecute();
						}
						//gửi sms nếu có class
						$classname = 'Qss_Bin_Notify_Sms_'.$form->FormCode.'_Deleted';
						if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
						{
							$trigger = new $classname($form);
							$trigger->init();
							$trigger->__doExecute();
						}
						//gửi validate nếu có class
						$classname = 'Qss_Bin_Notify_Validate_'.$form->FormCode.'_Deleted';
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
			if($bDeleted)
			{
				$rowdeleted++;
			}
		}
		if($this->isError())
		{
			$this->setMessage((count($i_IFID) - $rowdeleted) . '/'.count($i_IFID). ' ' .$this->_translate(2));
			$db->rollback();
		}
		else
		{
			$db->commit();
		}
	}

}