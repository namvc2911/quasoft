<?php
Class Qss_Service_Extra_Maintenance_Workorders_Delete extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
            $data  = array();
            $service = $this->services->Form->Remove('M759',$params['ifid'],$data);
            $common = new Qss_Model_Extra_Extra();

            if($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
//            else
//            {
//                $common->setStatus($params['rifid'], 1);
//            }
	}
}