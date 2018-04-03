<?php

class Qss_Service_Purchase_Receive_CreateOrder extends Qss_Lib_Service
{
    public function __doExecute ($params)
    {
        if(!isset($params['itemioid']) || !count($params['itemioid']))
        {
            $this->setError();
            $this->setMessage('B?n c?n ch?n ít nh?t m?t m?t hàng ?? t?o danh sách nh?n hàng!');
            return;
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
                $this->setMessage('B?n c?n có ít nh?t m?t hàng có s? l??ng l?n h?n 0 ?? t?o danh sách nh?n hàng!');
                return;
            }
        }
         
         
        $mCommon = new Qss_Model_Extra_Extra();
        $insert  = array();
        $i       = 0;
         
        foreach ($params['itemioid'] as $itemioid)
        {
            if($params['qty'][$i] > 0)
            {
                $insert['ODanhSachNhanHang'][$i]['SoYeuCau']  = (int)$params['requestioid'][$i];
                $insert['ODanhSachNhanHang'][$i]['MaMatHang'] = (int)$itemioid;
                $insert['ODanhSachNhanHang'][$i]['DonViTinh'] = (int)$params['uomioid'][$i];
                $insert['ODanhSachNhanHang'][$i]['SoLuong']   = $params['qty'][$i];
                $insert['ODanhSachNhanHang'][$i]['SoLuongLoi']   = $params['defect_qty'][$i];
            }
            $i++;
        }
         
        if(count($insert))
        {
            $service = $this->services->Form->Manual('M408',  $params['ifid'],  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        
            if(!$this->isError())
            {
                $this->setMessage('C?p nh?t thành công');
            }
        }
    }
}
