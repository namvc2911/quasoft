<?php

class Qss_Service_Extra_Warehouse_Input_Delete extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
			// code
			$delete['ODanhSachNhapKho'][] = $params['lineIOID'];
			$service =  $this->services->Form->Remove($params['module'], $params['ifid'], $delete);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}		

			// @todo: Cần save lại số đơn hàng trên đối tượng chính khi xóa dòng
		}
		else 
		{
			$this->setError();
		}
	}

	private function validate($params)
	{
		// @todo: Nếu chuyển sang bước 2 ko cho xóa
		$return = true;
		$extra  = new Qss_Model_Extra_Extra();
		$status = $extra->getStatusByIFID($params['insertModule'], $params['insertObject'], $params['ifid']);
		$lockStep = array(2);
		
		if(in_array($status, $lockStep))
		{
			$return = false;
			$this->setMessage('Không thể xóa do nhận hàng đã hoàn thành!');
		}
		
		return $return;
	}
}
?>