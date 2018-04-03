<?php

class Qss_Service_System_Form_Menu_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$form = new Qss_Model_System_Form($params['type']);
		if(!$form->saveMenuGroup($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>