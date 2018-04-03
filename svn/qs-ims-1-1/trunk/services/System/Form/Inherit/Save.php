<?php

class Qss_Service_System_Form_Inherit_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$form = new Qss_Model_System_Form();
		if(!$form->saveInherit($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>