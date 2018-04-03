<?php

class Qss_Service_System_Workflow_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$workflow  = new Qss_Model_System_Workflow($params['fid']);
		if((int) $params['WFID'])
		{
			$workflow->init((int) $params['WFID']);
		}
		if(!$workflow->save($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>