<?php
class Qss_Bin_Validation_M603_Step1 extends Qss_Lib_WValidation
{	
	/**
	 * Alert: Ngày yêu cầu đã đến hoặc quá hạn chưa?
	 */
	public function onAlert()
	{		
		parent::init();
		$deliveryDate = $this->_params->NgayYeuCau;
		$now          = date('Y-m-d');
		$now          = Qss_Lib_Date::i_fMysql2Time($now);
		$deliveryDate = Qss_Lib_Date::i_fMysql2Time($deliveryDate);
		
		if($now == $deliveryDate) 
		{
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");
		}
		elseif($now > $deliveryDate)
		{
			$this->setError();
			$this->setMessage("{$this->_translate(2)}");
		}
	}
}