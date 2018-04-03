<?php

class Qss_Service_Notify_Sms extends Qss_Lib_Service
{

	public function __doExecute (Qss_Model_Form $form,$classname)
	{
		if(class_exists($classname))
		{
			$notify = new $classname($form);
			$notify->init();
			$notify->__doExecute();
		}
	}
}
?>