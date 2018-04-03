<?php
Class Qss_Service_Extra_Mrp_Primary_Delete extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		$delete = isset($params['deleteid'])?$params['deleteid']:0;
		if($delete)
		{
			$delete        =  array('OKeHoachSanPham'=>array($delete));
			$removeService =  $this->services->Form->Remove($params['module'], $params['ifid'],$delete );
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
	

}