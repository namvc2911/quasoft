<?php
/**
 * Class Qss_Service_Purchase_Order_CreateSession
 * Tạo môt phiên xử lý mua hàng rỗng
 */
class Qss_Service_Purchase_Order_CreateSession extends Qss_Lib_Service
{
	public function __doExecute ($params)
	{
	    $insert['OPhienXuLyMuaHang'][0]['Ngay']     = date('Y-m-d');
	    $insert['OPhienXuLyMuaHang'][0]['Buoc']     = 1;
        $insert['OPhienXuLyMuaHang'][0]['BuocXuLy'] = 1;
	    
	    $service = $this->services->Form->Manual('M415',  0,  $insert, false);
	    if ($service->isError())
	    {
	        $this->setError();
	        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	    }
	    else 
	    {
	        $this->setStatus($service->getData());
	    }
	}
}