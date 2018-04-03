<?php
class Qss_Service_Purchase_Order_DeleteSession extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    $sessionifid = @(int)$params['sessionifid'];
	    
	    $mOrder  = new Qss_Model_Purchase_Order();
	    $session = $mOrder->getSessionByIFID($sessionifid);
	    
	    if($session && $session->Buoc >= 4)
	    {
	        $this->setError();
	        $this->setMessage('Xử lý mua hàng đã đến hoặc qua bước lập kế hoạch, bạn không thể xóa phiên! ');
	    }
	    else 
	    {
	        $service = $this->services->Form->Remove('M415',  $params['sessionifid'],  false);
	         
	        if ($service->isError())
	        {
	            $this->setError();
	            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	        }	        
	    }
	}
}