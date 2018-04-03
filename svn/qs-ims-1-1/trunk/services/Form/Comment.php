<?php

class Qss_Service_Form_Comment extends Qss_Lib_WService
{

	public function __doExecute (Qss_Model_Form $form, $user, $comment)
	{
		$form->comment($user->user_id,$comment);
		$classname = 'Qss_Bin_Notify_Mail_' . $form->FormCode.'_Comment';
		if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
		{
			$notify = new $classname($form);
			$notify->init();
			$toMails = $notify->__doExecute($user,$comment);
		}
		$classname = 'Qss_Bin_Notify_Sms_' . $form->FormCode.'_Comment';
		if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
		{
			$notify = new $classname($form);
			$notify->init();
			$toMails = $notify->__doExecute($user,$comment);
		}
		$classname = 'Qss_Bin_Notify_Validate_' . $form->FormCode.'_Comment';
		if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
		{
			$notify = new $classname($form);
			$notify->init();
			$toMails = $notify->__doExecute($user,$comment);
		}
	}

}
?>