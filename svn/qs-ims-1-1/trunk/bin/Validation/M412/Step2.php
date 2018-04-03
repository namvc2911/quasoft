<?php
class Qss_Bin_Validation_M412_Step2 extends Qss_Lib_WValidation
{
	public function onNext()
	{
		parent::init();
	    $yeuCauMuaSam = $this->_params->ODSYeuCauMuaSam;
	    
	    if(!count($yeuCauMuaSam))
	    {
	        $this->setError();
	        $this->setMessage('Chi tiết yêu cầu mua sắm bắt buộc.');
	    }
	}	
}