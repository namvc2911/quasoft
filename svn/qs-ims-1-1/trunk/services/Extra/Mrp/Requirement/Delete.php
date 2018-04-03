<?php

Class Qss_Service_Extra_Mrp_Requirement_Delete extends  Qss_Lib_Mrp_Service
{
	public function __doExecute($params)
	{
		//@todo: Phai kiem tra xem no da duoc tao ke hoach cung ung chua
		$delete = array();
                $common = new Qss_Model_Extra_Extra();
		$delete['OChiTietYeuCau'][0] = $params['ioid'];
		
		$service = $this->services->Form->Remove('M764' , $params['ifid'], $delete, false);
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
                else
                {
                      // get detail lines to save ref for main line
                    $this->_updateReferenceForRequirement($params);
                }
	}

}