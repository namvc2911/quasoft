<?php

class Qss_Bin_Validation_M706_Step1 extends Qss_Lib_WValidation
{
	/**
	 * @one: Kiểm tra xem lịch có trùng thời gian với lịch khác hay không?
	 */
	public function onAlert()
	{
		parent::init();
//		$start = $this->_params->NgayBatDau;
//		$today = date('Y-m-d');
//
//		if(Qss_Lib_Date::compareTwoDate($start, $today) != -1) // >=
//		{
//		    $this->setMessage('Thiết bị '.$this->_params->MaThietBi.' đến hạn điều động, bạn cần chuyển tình trạng áp dụng cho điều động thiết bị này. ');
//		    $this->setError();
//		}
	}
	

}
