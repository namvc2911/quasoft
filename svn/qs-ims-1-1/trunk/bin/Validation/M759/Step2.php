<?php
class Qss_Bin_Validation_M759_Step2 extends Qss_Lib_WValidation
{
    /**
     * next()
     * Cập nhật chi phí bảo trì
     */
	public function next()
	{
		parent::init();
    	$service = $this->services->Maintenance->WorkOrder->Cost->Update($this->_form->i_IFID);
        if ($service->isError())
        {
        	$this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
}