<?php

class Qss_Service_System_Form_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$form 		= new Qss_Model_System_Form($params['type']);
		if($form->b_fSave($params))
		{
			$step  		= new Qss_Model_System_Step(0);
			$step->FormCode = $form->FormCode;
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