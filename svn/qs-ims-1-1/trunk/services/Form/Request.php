<?php

class Qss_Service_Form_Request extends Qss_Lib_WService
{

	public function __doExecute (Qss_Model_Form $form, $stepno, $user, $comment)
	{
		$db = Qss_Db::getAdapter('main');
		$db->beginTransaction();
		$user_id = $user->user_id;
		$nextstep = new Qss_Model_System_Step($form->i_WorkFlowID);
		$nextstep->v_fInitByStepNumber($stepno);
		$cstep = new Qss_Model_System_Step($form->i_WorkFlowID);
		$cstep->v_fInitByStepNumber($form->i_Status);
		//check quyền
		$formrigths = $form->i_fGetRights($user->user_group_list);
		if(in_array($stepno, explode(',', $cstep->szNextStep)))
		{
			$nextrigths = $form->i_fGetRights($user->user_group_list, $stepno);
			if(!($formrigths&4) || !($nextrigths&16))
			{
				$this->setError(true);
				$this->setMessage('Bạn không được phép chuyển đến bước này');
			}
		}
		if(in_array($stepno, explode(',', $cstep->szBackStep)))
		{
			$backrigths = $form->i_fGetRights($user->user_group_list, $stepno);
			if(!($formrigths&4) || !($backrigths&32))
			{
				$this->setError(true);
				$this->setMessage('Bạn không được phép chuyển về bước này');
			}
		}
		
		$toMails = array();
		$ccMails = array();
		$domain = $_SERVER['HTTP_HOST'];
		$users = $form->getRefUsers();
		$address = array();
		foreach ($users as $item)
		{
			if($user_id == $item->UID)
			{
				$toMails[$item->EMail] = $item->UserName;
			}
			else
			{
				$ccMails[$item->EMail] = $item->UserName;
			}
		}
		if($user_id && !count($toMails))
		{
			$usermodel = new Qss_Model_Admin_User();
			$usermodel->init($user_id);
			$toMails[$usermodel->szEMail] = $usermodel->szUserName;
		}
		if($stepno != $form->i_Status && !in_array($stepno, explode(',', $cstep->szNextStep)) && !in_array($stepno, explode(',', $cstep->szBackStep)))
		{
			$this->setError();
			$this->setMessage(sprintf('%1$s %3$s (%2$s)',$this->_translate(160),$form->sz_Name,$nextstep->szStepName));
		}
		$approvers = $cstep->getApproverByIFID($form->i_IFID);
		if($cstep->intStepType && in_array($stepno, explode(',', $cstep->szNextStep)))//có yêu cầu phê duyệt và chuyển sang bước tiếp theo thì check phê duyệt
		{
			$ok = true;//sao trước lại false
			foreach ($approvers as $item)
			{
				if(!$item->StepNo)
				{
					$this->setMessage($item->Name.' chưa duyệt');
				}
				if($cstep->intStepType == 1 || $cstep->intStepType == 2)//yêu cầu tất cả được duyệt, có StepNo là đã được duyệt
				{
					if(!$item->StepNo)
					{
						$ok = false;
						break;
					}
				}
				elseif($cstep->intStepType == 3)//Yêu cầu cái cuối cùng
				{
					if(!$item->StepNo)
					{
						$ok = false;
					}
					else
					{
						$ok = true;
					}
				}
				elseif($cstep->intStepType == 4)//Chỉ cần 1 nhóm duyệt
				{
					if($item->StepNo)
					{
						$ok = true;
						break;
					}
				}
			}
			if(!$ok)
			{
				$this->setError();
			}
		}
		if($cstep->intStepType && in_array($stepno, explode(',', $cstep->szBackStep)))//có yêu cầu phê duyệt và chuyển sang bước trước đó, yêu cầu phải bỏ duyệt
		{
			$ok = true;
			foreach ($approvers as $item)
			{
				if($item->StepNo)
				{
					$ok = false;
					$this->setMessage($item->Name.' đã duyệt bởi '.$item->UserName);
				}
			}
			if(!$ok)
			{
				$this->setError();
			}
		}
		if(!$this->isError())//change status
		{
			//validation
			$this->workflowValidate($form,$stepno,$cstep);
			//tranfer
			if(!$this->isError())
			{
				//change status
				$form->changeStatus($stepno);
				$this->workflowTransfer($form,$stepno,$cstep);
				if(!$this->isError())
				{
					// run bash
					$this->workflowBash($form,$stepno,$cstep);
					//trace
					$comment = $nextstep->szStepName.': '.$comment;
					$form->v_fTrace($user_id,$comment);//
					$form->updateReader($user_id);
					$classname = 'Qss_Bin_Notify_Mail_' . $form->FormCode.'_Step'.$stepno;
					if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
					{
						$notify = new $classname($form);
						$notify->init($nextstep);
						$toMails = $notify->__doExecute($user,$nextstep->szStepName,$comment);
					}
					$classname = 'Qss_Bin_Notify_Sms_' . $form->FormCode.'_Step'.$stepno;
					if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
					{
						$notify = new $classname($form);
						$notify->init($nextstep);
						$toMails = $notify->__doExecute($user,$nextstep->szStepName,$comment);
					}
					$classname = 'Qss_Bin_Notify_Validate_' . $form->FormCode.'_Step'.$stepno;
					if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
					{
						$notify = new $classname($form);
						$notify->init($nextstep);
						$notify->__doExecute($user,$nextstep->szStepName,$comment);
					}
				}
			}

		}
		if(!$this->isError())
		{
			//if($cstep->intStepType || $nextstep->intStepType)
			{
				$form->updateApproveCount();
			}
			$form->setModify();
			//update các loại về duyệt
			$db->commit();
		}
		else
		{
			$db->rollback();
		}
	}
}
?>