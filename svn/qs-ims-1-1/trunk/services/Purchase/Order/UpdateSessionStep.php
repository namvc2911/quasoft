<?php
// Chuyen buoc cua session
class Qss_Service_Purchase_Order_UpdateSessionStep extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    if(!isset($params['tostep']) || !isset($params['sessionifid']))
	    {
	        return;
	    }
	    
	    $insert                                     = array();
	    $insert['OPhienXuLyMuaHang'][0]['Buoc']     = $params['tostep'];
        $insert['OPhienXuLyMuaHang'][0]['BuocXuLy'] = $params['tostepxuly'];
	    
	    $service = $this->services->Form->Manual('M415',  $params['sessionifid'],  $insert, false);
	    
	    if ($service->isError())
	    {
	        $this->setError();
	        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	    }
	    else 
	    {
	        // Update lai vao session
	        $_SESSION['M415Step'] = $params['tostep'];	        
	    }
	}
}