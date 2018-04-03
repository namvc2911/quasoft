<?php

class Qss_Service_Extra_Warehouse_Output_Delete extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
			// code
			$delete['ODanhSachXuatKho'][] = $params['lineIOID'];
			$service =  $this->services->Form->Remove($params['module'], $params['ifid'], $delete);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}				
		}
		else 
		{
			$this->setError();
		}
	}

	private function validate($params)
	{
		$return = true;
		$extra  = new Qss_Model_Extra_Extra();
		$status = $extra->getStatusByIFID($params['insertModule'], $params['insertObject'], $params['ifid']);
		$lockStep = array(2);
		
		if(in_array($status, $lockStep))
		{
			$return = false;
			$this->setMessage('Không thể xóa do chuyển hàng đã được hoàn thành!');
		}
		return $return;
	}
}
?>