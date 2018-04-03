<?php

class Qss_Service_Notify_Validate extends Qss_Lib_Service
{

	public function __doExecute (Qss_Model_Form $form,$classname)
	{
		//$classname = 'Qss_Bin_Notify_Mail_' . $form->FormCode;
		if(class_exists($classname) && !@constant($classname.'::INACTIVE'))
		{
			$notify = new $classname($form);
			$notify->init();
			$notify->__doExecute();
			if($notify->isError())
			{
				$this->setError();
				$this->setMessage($notify->getMessage());
			}
		}
	}
}
?>