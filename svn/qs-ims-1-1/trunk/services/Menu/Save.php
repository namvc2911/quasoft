<?php

class Qss_Service_Menu_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$menu = new Qss_Model_Menu();
		if(!$menu->save($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>