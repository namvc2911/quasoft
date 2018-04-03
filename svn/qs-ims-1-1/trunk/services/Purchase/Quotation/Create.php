<?php

class Qss_Service_Purchase_Quotation_Create extends Qss_Lib_Service
{

	public function __doExecute ($params)
	{
	    //echo '<pre>'; print_r($params); die;
	    $AllQtyZero = 1;
	    $keepItem   = array();
	    $k          = 0;
	    
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
	            if(!key_exists($itemioid, $keepItem))
	            {
	                $keepItem[$itemioid]                     = $k;
	                $insert['ODSBGMuaHang'][$k]['MaSP']      = (int)$itemioid;
	                $insert['ODSBGMuaHang'][$k]['DonViTinh'] = (int)$params['uomioid'][$i];
	                $insert['ODSBGMuaHang'][$k]['DonGia']    = isset($params['price'][$i])?$params['price'][$i]:0;	      
	                $insert['ODSBGMuaHang'][$k]['KyThuat']   = (int)0;
	                $insert['ODSBGMuaHang'][$k]['ChatLuong'] = (int)2;
	                $insert['ODSBGMuaHang'][$k]['ThoiGian']  = 1;	        
	                $insert['ODSBGMuaHang'][$k]['SoLuong']   = $params['qty'][$i];
	                $k++;
	            }
	            else 
	            {
	                $insert['ODSBGMuaHang'][$keepItem[$itemioid]]['SoLuong']  += $params['qty'][$i];
	            }
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
//	            $ifid    = $service->getData();
//	            $baoGia = $mCommon->getTableFetchOne('OBaoGiaMuaHang', array('IFID_M406'=>$ifid));
//
//	            if($baoGia)
//	            {
//	                $this->setMessage(
//	                    'Báo giá "'
//	                    .$baoGia->SoChungTu
//	                    .'" với nhà cung cấp "'
//	                    . $baoGia->MaNCC
//	                    . '" - "'.$baoGia->TenNCC .'" đã được tạo! '
//                    );
//	            }
	             
	        }	        
	    }	     
	}
}
?>