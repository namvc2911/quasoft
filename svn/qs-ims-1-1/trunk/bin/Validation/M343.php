<?php
class Qss_Bin_Validation_M343 extends Qss_Lib_Validation
{
	public function onValidated()
	{
		parent::init();
		$start      = $this->_params->NgayBatDau; /* Ngày bắt đầu */
		$end        = $this->_params->NgayKetThuc; /* Ngày kết thúc */
		$microStart = strtotime($start); /* Chuyển Ngày bắt đầu sang dạng có thể so sánh */
		$microEnd   = strtotime($end);  /* Chuyển Ngày kết thúc sang dạng có thể so sánh */
		$now 		= strtotime("now");  /* Ngày hiện tại */      
		$update      = array();
		$active      = $this->_params->HoatDong;
		
		
		
		if(($microEnd && $microEnd < $now) ||  ($microStart > $now))
		{
			$update['OBoPhan'][0]['HoatDong'] = 0;
			$update['OBoPhan'][0]['ioid']     = $this->_params->IOID;
			$this->setError();			
		}
		else 
		{
			$update['OBoPhan'][0]['HoatDong'] = 1;
			$update['OBoPhan'][0]['ioid']     = $this->_params->IOID;
		}
		
		if(!$active || (($microEnd && $microEnd < $now) ||  ($microStart > $now)))
		{
			$service = $this->services->Form->Manual('M343' ,$this->_params->IFID_M343,$update,false);
		}
	}
}