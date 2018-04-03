<?php

class Qss_Service_System_Workflow_Step_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$step  = new Qss_Model_System_Step($params['wfid']);
		if((int) $params['SID'])
		{
			$step->v_fInit((int) $params['SID']);
		}
		if($step->save($params))
		{
				//print_r();die;
			$step->deleteStepActivities();
			$step->deleteStepDocuments();
			if(isset($params['document_list']))
			{
				foreach($params['document_list'] as $v)
				{
					$step->updateStepDocuments($v);
				}
			}
			if(isset($params['activity_list']))
			{
				foreach($params['activity_list'] as $v)
				{
					$step->updateStepActivities($v);
				}	
			}
		}
		else
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
		}
	}

}
?>