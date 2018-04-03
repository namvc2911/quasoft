<?php
class Qss_Bin_Validation_M317_Step2 extends Qss_Lib_WValidation
{
	public function onMove()
	{
		parent::init();
		$model = new Qss_Model_M079_Main();
		if($model->checkPeriodClose($this->_params->Ref_KyCong, $this->_params->Ref_PhongBanHienTai))	
		{	
			$this->setError();
	        $this->setMessage("Đã đóng kỳ công");
		}
	}
	public function onNext()
	{
		parent::init();
		$model = new Qss_Model_M079_Main();
		if($model->checkPeriodClose($this->_params->Ref_KyCong, $this->_params->Ref_PhongBanHienTai))	
		{	
			$this->setError();
	        $this->setMessage("Đã đóng kỳ công");
		}
	}
}