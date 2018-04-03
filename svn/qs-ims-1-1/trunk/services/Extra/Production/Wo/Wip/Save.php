<?php

class Qss_Service_Extra_Production_Wo_Wip_Save extends Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		$error = $this->validate_save($params);
		if($error)
		{
			$this->setError();
			$this->setMessage($error);
		}
		else
		{
			$insert = array();
			$insert['OPhieuGiaoViec'][0]['SoLuong'] = $params['qty'];
			
			$service = $this->services->Form->Manual('M712' , $params['wifid'], $insert, false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
			if(!$this->isError())
			{
				$this->setMessage('Cập nhật thành công!');
			}
		}
	}
	
	private function validate_save($params)
	{
		$msg = '';
		if(!isset($params['qty']) ||  (!is_numeric($params['qty'])))
		{
			$msg .= 'Số lượng cập nhật chưa được điền hoặc không phải là số!';
		}
		
		if(!isset($params['wifid']) ||  (!is_numeric($params['wifid'])))
		{
			$msg .= 'Đã có lỗi xảy ra!';// thieu ifid
		}
		return $msg;
	}
}

?>