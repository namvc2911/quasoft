<?php

class Qss_Service_Form_Stop extends Qss_Lib_Service
{

	public function __doExecute (Qss_Model_Form $form, $traceid)
	{
		$user_id = Qss_Register::get('userinfo')->user_id;
		$trace = $form->getTraceByID($traceid);
		if($user_id == $trace->UID && $trace->Accepted == 0)
		{
			$form->stopVerify($traceid);
			$form->unLock();
			// send mail
			$domain = $_SERVER['HTTP_HOST'];
			$toMails = array();
			$ccMails = array();
			$users = $form->getRefUsers();
			foreach ($users as $item)
			{
				if($trace->ToUID == $item->UID)
				{
					$toMails[$item->EMail] = $item->UserName;
				}
				else
				{
					$ccMails[$item->EMail] = $item->UserName;
				}
			}
			$body = sprintf('Chào bạn!
				 <p>%1$s cập nhật tình trạng công việc %2$s
				 <a href="http://%3$s/user/form/edit?ifid=%4$d&deptid=%5$d">Xem chi tiết</a>
				 <p>', 
				 Qss_Register::get('userinfo')->user_name,
				 $form->sz_Name, 
				 $domain, 
				 $form->i_IFID,
				 $form->i_DepartmentID);
			$form->updateReader($user_id);
			$this->sendMail($form->sz_Name, $toMails, $body,$ccMails);
			$form->setModify();
		}
		else
		{
			$this->setMessage($this->_translate(146));
			$this->setError();
		}
	}
}
?>