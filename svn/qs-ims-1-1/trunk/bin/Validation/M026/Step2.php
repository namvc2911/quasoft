<?php
class Qss_Bin_Validation_M026_Step2 extends Qss_Lib_WValidation
{
	public function onMove()
	{
		parent::init();
		$model = new Qss_Model_M079_Main();
		$kycong = $model->getPeriod($this->_params->NgayCong);
		if(!$kycong || $kycong->Status == 2)
		{
			$this->setError();
        	$this->setMessage("Kỳ công đã đóng hoặc chưa mở");
		} 
		if($model->checkPeriodClose($kycong->IOID, $this->_params->Ref_PhongBanHienTai))	
		{	
			$this->setError();
	        $this->setMessage("Đã đóng kỳ công");
		}
	}
	public function onNext()
	{
		parent::init();
		$model = new Qss_Model_M079_Main();
		$kycong = $model->getPeriod($this->_params->NgayCong);
		if(!$kycong || $kycong->Status == 2)
		{
			$this->setError();
        	$this->setMessage("Kỳ công đã đóng hoặc chưa mở");
		} 
		if($model->checkPeriodClose($kycong->IOID, $this->_params->Ref_PhongBanHienTai))	
		{	
			$this->setError();
	        $this->setMessage("Đã đóng kỳ công");
		}
	}
}