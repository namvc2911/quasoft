<?php

class Qss_Service_Event_Log_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$event = new Qss_Model_Event();
		
		$today  = strtotime(date("d-m-Y"));
		$xDay   = strtotime($params['date']);
		$etime  = strtotime(Qss_Lib_Date::formatTime($params['etime']));
		$stime  = strtotime(Qss_Lib_Date::formatTime($params['stime']));
		$status = (isset($params['status']))?$params['status']:0;
		
		if($etime < $stime)
		{
			$this->setError();
			$this->setMessage('Thời gian kết thúc phải nhỏ hơn hoặc bằng thời gian bắt đầu.');
		}
		if($today < $xDay)
		{
			if($status)
			{
				$this->setError();
				$this->setMessage('Thời điểm kết thúc nằm trong tương lai, bạn không thể chọn hoàn thành.');
			}
		}
		elseif($today == $xDay)
		{
			if($etime > time())
			{
				if($status)
				{
					$this->setError();
					$this->setMessage('Thời điểm kết thúc nằm trong tương lai, bạn không thể chọn hoàn thành.');
				}
			}
		}
		if(!$this->isError())
		{
			$event->saveLog($params);
		}
		
	}

}
?>