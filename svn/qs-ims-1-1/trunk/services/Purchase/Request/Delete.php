<?php
class Qss_Service_Purchase_Request_Delete extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    // Xoa du lieu gom hai buoc
	    // b1: Xoa trong phien xu ly mua hang
	    // b2: Xoa trong yeu cau
	    $mOrder      = new Qss_Model_Purchase_Order();
	    $requestIFID = isset($params['requestIFID'])?$params['requestIFID']:0;
	    $requestIOID = isset($params['requestIOID'])?$params['requestIOID']:0;
	    $sessionIFID = isset($params['sessionIFID'])?$params['sessionIFID']:0;
	    $request     = $mOrder->getRequestInSession($requestIOID, $sessionIFID);
	    
	    
        // Xoa du lieu trong phien mua hang	    
	    if($request)
	    {
	        $move = array('OYeuCauPhienXLMH'=>array($request->IOID));
	        
	        $service1 = $this->services->Form->Remove('M415', $sessionIFID, $move);
	         
	        if($service1->isError())
	        {
	            $this->setError();
	            $this->setMessage($service1->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	        }	        
	    }
	    
	    // Xoa du lieu trong yeu cau mua hang
	    if($requestIFID)
	    {
	        $service = $this->services->Form->Remove('M412', $requestIFID, false);
	        
	        if($service->isError())
	        {
	            $this->setError();
	            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	        }	        
	    }
	}
}