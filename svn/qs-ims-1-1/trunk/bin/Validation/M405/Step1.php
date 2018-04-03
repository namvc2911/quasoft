<?php
class Qss_Bin_Validation_M405_Step1 extends Qss_Lib_WValidation
{
	/**
	 *	Kiểm tra xem yêu cầu mua hàng đã quá hạn hay chưa
	 */
	public function onAlert()
	{
		parent::init();
		$dueDate = $this->_params->NgayYeuCau;
		$now = time();
		if($now > Qss_Lib_Date::i_fMysql2Time($dueDate)) 
		{
			$this->setError();
			$this->setMessage($this->_translate(2));
		}
		elseif($now == Qss_Lib_Date::i_fMysql2Time($dueDate))
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
	}
}