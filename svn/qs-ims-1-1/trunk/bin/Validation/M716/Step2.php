<?php
class Qss_Bin_Validation_M716_Step2 extends Qss_Lib_WValidation
{
	public function onNext()
	{
		parent::init();
		if(Qss_Lib_System::objectInForm('M716', 'ODSKeHoachMuaSam'))
		{
		    $keHoachMuaSam = $this->_params->ODSKeHoachMuaSam;
		    
		    if(!count($keHoachMuaSam))
		    {
		        $this->setError();
		        $this->setMessage('Chi tiết kế hoạch mua sắm không bắt buộc');
		    }
		}
		elseif(Qss_Lib_System::objectInForm('M716', 'ODSNhuCauMuaHang'))
		{
		    $nhuCauMuaHang = $this->_params->ODSNhuCauMuaHang;
		    
		    if(!count($nhuCauMuaHang))
		    {
		        $this->setError();
		        $this->setMessage('Chi tiết nhu cầu mua hàng bắt buộc.');
		    }		    
		}
	}	
}