<?php

class Qss_Service_Notify_Mail extends Qss_Lib_Service
{

	public function __doExecute (Qss_Model_Form $form,$classname)
	{
		//$classname = 'Qss_Bin_Notify_Mail_' . $form->FormCode;
		if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
		{
			$notify = new $classname($form);
			$notify->init();
			$notify->__doExecute();
		}
	}
}
?>