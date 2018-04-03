<?php

class Qss_Service_System_Field_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$field = new Qss_Model_System_Field();
		if(!$field->b_fSave($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>