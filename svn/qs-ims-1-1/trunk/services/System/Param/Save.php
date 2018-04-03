<?php

class Qss_Service_System_Param_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params,$deptid)
	{
		$param = new Qss_Model_System_Param();
		if(!$param->save($params,$deptid))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>