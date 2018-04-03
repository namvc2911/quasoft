<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_WService extends Qss_Lib_Service
{

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */
	protected function workflowBash($form,$stepno,$cstep)
	{
		if(in_array($stepno, explode(',', $cstep->szNextStep)))
		{
			$bashmodel = new Qss_Model_Bash();
			$bash = $bashmodel->getByFormAndStep($form->FormCode,$stepno);
			foreach ($bash as $item)
			{
				$service = new Qss_Service();
				$service->Bash->Execute($form,$item->BID);
			}
		}
	}
	protected function workflowValidate($form,$stepno,$cstep)
	{
		//validation for form
		$classname = 'Qss_Bin_Validation_'.$form->FormCode;
		if(class_exists($classname))
		{
			$validation = new $classname($form);
			$validation->onValidated();
			if($validation->isError())
			{
				$this->setError();
				$this->setMessage($validation->getMessage());
			}
		}
		//validation for next step
		$classname = 'Qss_Bin_Validation_' . $form->FormCode . '_' . 'Step' . $stepno;
	
		if(class_exists($classname))
		{
			$validation = new $classname($form);
		}
		else 
		{
			$validation = new Qss_Lib_WValidation($form);
		}
		if(in_array($stepno, explode(',', $cstep->szNextStep)))
		{
			$validation->onNext();
		}
		elseif(in_array($stepno, explode(',', $cstep->szBackStep)))
		{
			$validation->onBack();
		}
		if($validation->isError())
		{
			$this->setError();
		}
		$this->setMessage($validation->getMessage());
		//validation for current step
		$classname = 'Qss_Bin_Validation_' . $form->FormCode . '_' . 'Step' . $cstep->intStepNo;
	
		if(class_exists($classname))
		{
			$validation = new $classname($form);
			$validation->onMove();
			if($validation->isError())
			{
				$this->setError();
				$this->setMessage($validation->getMessage());
			}
		}
		if($this->isError())
		{
			$form->updateError($this->isError(), $this->getMessage());
		}
	}
	protected function workflowTransfer($form,$stepno,$cstep)
	{
		//execute for step
		$classname = 'Qss_Bin_Validation_' . $form->FormCode . '_' . 'Step' . $stepno;
		if(class_exists($classname))
		{
			$validation = new $classname($form);
			if(in_array($stepno, explode(',', $cstep->szNextStep)))
			{
				$validation->next();
				
			}
			elseif(in_array($stepno, explode(',', $cstep->szBackStep)))
			{
				$validation->back();
			}
			if($validation->isError())
			{
				$this->setError();
			}
			$this->setMessage($validation->getMessage());
		}
		$classname = 'Qss_Bin_Validation_' . $form->FormCode . '_' . 'Step' . $cstep->intStepNo;
		if(class_exists($classname))
		{
			$validation = new $classname($form);
			$validation->move();
			$this->setMessage($validation->getMessage());
		}
		if($this->isError())
		{
			$form->updateError($this->isError(), $this->getMessage());
		}
	}
}
?>