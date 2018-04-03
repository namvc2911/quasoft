<?php

class Qss_Service_Purchase_Order_UpdateOrder extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    $line      = $params['line'];
	    $dateName  = 'date'.$line;
	    $dateVal   = $params[$dateName];
	    $orderIFID = $params['orderifid'];
	    
	    $update['ODonMuaHang'][0]['NgayDatHang'] = $dateVal;
	    
	    
	    $service = $this->services->Form->Manual('M406',  $orderIFID,  $update, false);
	    
	    if ($service->isError())
	    {
	        $this->setError();
	        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	    }	    
	}
}