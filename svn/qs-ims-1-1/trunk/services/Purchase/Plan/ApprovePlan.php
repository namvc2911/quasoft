<?php
// Sau khi cap nhat ke hoach, cap nhat lai vao session, sau tra lai IOID cua ke hoach bang setStatus
class Qss_Service_Purchase_Plan_ApprovePlan extends Qss_Lib_Service
{

	public function __doExecute ($params)
	{
        if(!isset($params['PlanIFID']) && $params['PlanIFID'])
        {
            $this->setError();
            $this->setMessage('Kế hoạch chưa được lưu lại!');
        }

        if(!$this->isError())
        {
            $form     = new Qss_Model_Form();
            $form->initData($params['PlanIFID'], $params['DeptID']);
            $service2 = $this->services->Form->Request($form, 2, $params['User'], '');
            
            if ($service2->isError())
            {
                $this->setError();
                $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }            
        }
	}
}
?>