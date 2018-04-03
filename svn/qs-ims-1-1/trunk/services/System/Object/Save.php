<?php

class Qss_Service_System_Object_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$object = new Qss_Model_System_Object();
		if(!$object->b_fSave($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>