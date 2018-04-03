<?php
class Qss_Bin_Validation_M509_Step1 extends Qss_Lib_WValidation
{
	/**
	 *	Kiểm tra xem báo giá bán hàng đã quá hạn hay chưa
	 */
	public function onAlert()
	{
		parent::init();
		$dueDate = $this->_params->NgayYeuCau;
		$now = time();
		if($now > Qss_Lib_Date::i_fMysql2Time($dueDate)) 
		{
			$this->setError();
			$this->setMessage('Báo giá bán hàng đã quá hạn, hãy kiểm tra việc báo giá.');
		}
	}
}