<?php

class Qss_Service_Inventory_Input_InsertDetail extends Qss_Lib_Service
{
    public function __doExecute ($params)
    {
        if(!isset($params['itemioid']) || !count($params['itemioid']))
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một dòng dữ liệu để cập nhật!');
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
                $this->setMessage('Cần ít nhất một dòng có số lượng lớn hơn 0 để cập nhật!');
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
                $insert['ODanhSachNhapKho'][$i]['SoYeuCau']  = (int)$params['requestioid'][$i];
                $insert['ODanhSachNhapKho'][$i]['MaSanPham'] = (int)$itemioid;
                $insert['ODanhSachNhapKho'][$i]['DonViTinh'] = (int)$params['uomioid'][$i];
                $insert['ODanhSachNhapKho'][$i]['SoLuong']   = $params['qty'][$i];
            }
            $i++;
        }
         
        if(count($insert))
        {
            $service = $this->services->Form->Manual('M402',  $params['ifid'],  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }
    }
}
