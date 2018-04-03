<?php

class Qss_Service_Purchase_Quotation_Createquotation extends Qss_Lib_Service
{

	public function __doExecute ($params)
	{
	    $AllQtyZero = 1;
	    
        if(!isset($params['itemioid']) || !count($params['itemioid']))
        {
            $this->setError();
            $this->setMessage('Bạn cần chọn ít nhất một mặt hàng để tạo báo giá!');
        }
        
        if(!isset($params['partnerioid']) || !$params['partnerioid'])
        {
            $this->setError();
            $this->setMessage('Nhà cung cấp chưa được chọn!');
        }  
        
        if(isset($params['qty']))
        {
            $AllQtyZero = 1;
        
            foreach($params['qty'] as $qty)
            {
                if($qty > 0)
                {
                    $AllQtyZero = 0;
                }
            }
             
            if($AllQtyZero == 1)
            {
                $this->setError();
                $this->setMessage('Bạn cần có ít nhất mặt hàng có số lượng lớn hơn 0 để tạo báo giá!');
            }
        }        
        
        if($this->isError())
        {
            return;
        }

        
        $mCommon = new Qss_Model_Extra_Extra();
        $mObject = new Qss_Model_Object();
        $mObject->v_fInit('OBaoGiaMuaHang', 'M406');
        
	    $insert  = array();
	    $i       = 0;
	    	   
	    
	    $insert['OBaoGiaMuaHang'][0]['SoPhieu']     = Qss_Lib_Extra::getDocumentNo($mObject);
	    $insert['OBaoGiaMuaHang'][0]['MaNCC']       = @(int)$params['partnerioid'];
	    $insert['OBaoGiaMuaHang'][0]['SoKeHoach']   = @(int)$params['planioid'];
	    $insert['OBaoGiaMuaHang'][0]['NVMuaHang']   = $params['UserName'];
	    $insert['OBaoGiaMuaHang'][0]['NgayYeuCau']  = date('Y-m-d');
	    $insert['OBaoGiaMuaHang'][0]['NgayBaoGia']  = date('Y-m-d');
	    
	    foreach ($params['itemioid'] as $itemioid) 
	    {	        
	        if($params['qty'][$i]> 0)
	        {
	            $insert['ODSBGMuaHang'][$i]['MaSP']      = (int)$itemioid;
	            $insert['ODSBGMuaHang'][$i]['DonViTinh'] = (int)$params['uomioid'][$i];
	            $insert['ODSBGMuaHang'][$i]['DonGia']    = $params['price'][$i];
	            $insert['ODSBGMuaHang'][$i]['SoLuong']   = $params['qty'][$i];
	            $insert['ODSBGMuaHang'][$i]['KyThuat']   = (int)1;
	            $insert['ODSBGMuaHang'][$i]['ChatLuong'] = (int)2;
	            $insert['ODSBGMuaHang'][$i]['ThoiGian']  = 1;	            
	        }
	        $i++;
	    }	    
	    
	    
	    if(count($insert))
	    {
	        $service = $this->services->Form->Manual('M406',  0,  $insert, false);
	        if ($service->isError())
	        {
	            $this->setError();
	            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
	        }	 

	        if(!$this->isError())
	        {
	            $ifid    = $service->getData();
	            $baoGia = $mCommon->getTableFetchOne('OBaoGiaMuaHang', array('IFID_M406'=>$ifid));
	             
	            if($baoGia)
	            {
	                $this->setMessage(
	                    'Báo giá "'
	                    .$baoGia->SoChungTu
	                    .'" với nhà cung cấp "'
	                    . $baoGia->MaNCC 
	                    . '" đã được tạo! '
                    );
	            }
	             
	        }	        
	    }	     
	}
}
?>