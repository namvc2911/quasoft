<?php

class Qss_Service_System_Form_Object_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$form = new Qss_Model_System_Form();
		$form->b_fInit($params['fid']);
		if(!$form->b_fSaveFormObject($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>