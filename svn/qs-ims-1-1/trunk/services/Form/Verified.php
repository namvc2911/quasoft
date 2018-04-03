<?php

class Qss_Service_Form_Verified extends Qss_Lib_WService
{

	public function __doExecute (Qss_Model_Form $form, $traceid,$accepted, $comment)
	{
		$user_id = Qss_Register::get('userinfo')->user_id;
		
		$trace = $form->getTraceByID($traceid);
		
		$fromstep = $trace->Status;
		$tostep = $trace->ToStatus;
		$nextstep = new Qss_Model_System_Step($form->i_WorkFlowID);
		$nextstep->v_fInitByStepNumber($tostep);
		$cstep = new Qss_Model_System_Step($form->i_WorkFlowID);
		$cstep->v_fInitByStepNumber($form->i_Status);
		
		$toMails = array();
		$ccMails = array();
		$users = $form->getRefUsers();
		foreach ($users as $item)
		{
			if($trace->UID == $item->UID)
			{
				$toMails[$item->EMail] = $item->UserName;
			}
			else
			{
				$ccMails[$item->EMail] = $item->UserName;
			}
		}
		//check if change status
		if($fromstep != $form->i_Status || $trace->Accepted == 4 || $trace->ToUID != $user_id || $form->verifyid != $user_id)
		{
			$this->setError();
			$this->setMessage('Đã có người thay đổi qui trình, bạn không thực hiện được hành động này ');
		}
		//owner, for return request or reverify.
		elseif($fromstep != $tostep)
		{
			if($accepted == 1)
			{
				//validation
				$this->workflowValidate($form,$tostep,$cstep);
				//tranfer
				if(!$this->isError())
				{
					$this->workflowTransfer($form,$tostep,$cstep);
					if(!$this->isError())
					{
						//change status 
						$form->changeStatus($tostep);
						//change user if implement
						if($nextstep->intStepType == 2 && $cstep->intStepType == 2)
						{
							$form->changeUser($trace->ToUID);
						}
						// run bash
						if(!$this->isError())
						{
							$this->workflowBash($form,$tostep,$cstep);
						}
					}
				}
			}
			//unlock
			$form->unLock();
			//verify
			$this->verify($form,$traceid,$accepted,$comment,$toMails,$ccMails);
		}
		//for assign
		elseif($fromstep == $tostep)
		{
			if($accepted == 1)
			{
				//change user
				$form->changeUser($user_id);
			}
			//unlock
			$form->unLock();
			//verify
			$this->verify($form,$traceid,$accepted,$comment,$toMails,$ccMails);
		}
		else
		{
			$this->setMessage($this->_translate(146));
			$this->setError();
		}
		if(!$this->isError())
		{
			$form->setModify();
		}
	}
	protected function verify($form,$traceid,$accepted,$comment,$toMails,$ccMails)
	{
		$domain = $_SERVER['HTTP_HOST'];
		$form->v_fVerify($traceid,$accepted,$comment);
		$form->updateReader(Qss_Register::get('userinfo')->user_id);
		// send mail
		$body = sprintf('Chào bạn!
				 <p>%1$s cập nhật tình trạng công việc %2$s
				 <a href="http://%3$s/user/form/edit?ifid=%4$d&deptid=%5$d">Xem chi tiết</a>
				 <p>', 
				 Qss_Register::get('userinfo')->user_name,
				 $form->sz_Name, 
				 $domain, 
				 $form->i_IFID,
				 $form->i_DepartmentID);
		$this->sendMail($form->sz_Name, $toMails, $body,$ccMails);
	}
}
?>