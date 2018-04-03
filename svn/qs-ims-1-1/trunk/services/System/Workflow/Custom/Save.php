<?php

class Qss_Service_System_Workflow_Custom_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$step  = new Qss_Model_System_Step($params['WFID']);
		if((int) $params['SID'])
		{
			$step->v_fInit((int) $params['SID']);
		}
		if(!$step->saveCustom($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>