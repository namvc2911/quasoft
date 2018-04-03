<?php

class Qss_Service_System_Language_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$model = new Qss_Model_System_Language();
		if(!$model->save($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>