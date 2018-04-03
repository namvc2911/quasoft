<?php
class Qss_Service_Purchase_Order_CreateOrders extends Qss_Lib_Service
{
    public function __doExecute ($params)
    {
        $j        = 0;
        $inserts  = array();
        $mRequest = new Qss_Model_Purchase_Request();
        $ordered  = (isset($params['Ordered']))?$params['Ordered']:false;

        // Validate
        if(!isset($params['itemioid']) || !count($params['itemioid']))
        {
            $this->setError();
            $this->setMessage('Bạn cần chọn ít nhất một mặt hàng!');
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
                $this->setMessage('Bạn cần chọn ít nhất một mặt hàng có số lượng lớn hơn 0!');
                return;
            }
        }

        // Check
        $requests = $mRequest->getRequestsBySession($params['sessionifid']);
        $allMatch = true;

        foreach($requests as $item)
        {
            $k     = 0;
            $match = false;
            foreach($params['itemioid'] as $itemioid)
            {
                if($itemioid == $item->ItemIOID
                    && $params['requestioid'][$k] == $item->RequestIOID
                    && $params['uomioid'][$k] == $item->UomIOID)
                {
                    $match = true;
                }
                $k++;
            }

            if(!$match)
            {
                $allMatch = false;
                break;
            }
        }

        // Insert
        foreach($params['itemioid'] as $itemioid)
        {
            if(isset($params['partnerioid'][$j]) && $params['partnerioid'][$j])
            {
                if(!isset($inserts[$params['partnerioid'][$j]]))
                {
                    $i = 0;
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['MaNCC']       = @(int)$params['partnerioid'][$j];
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['SoKeHoach']   = @(int)$params['planioid'][$j];
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['LoaiTien']    = $params['currency'][$j];
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['NVMuaHang']   = $params['UserName'];
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['NgayDatHang'] = date('Y-m-d');
                    $inserts[$params['partnerioid'][$j]]['ODonMuaHang'][0]['NgayYCNH']    = date('Y-m-d');
                }

                $inserts[$params['partnerioid'][$j]]['ODSDonMuaHang'][$i]['SoYeuCau']  = (int)$params['requestioid'][$j];
                $inserts[$params['partnerioid'][$j]]['ODSDonMuaHang'][$i]['MaSP']      = (int)$itemioid;
                $inserts[$params['partnerioid'][$j]]['ODSDonMuaHang'][$i]['DonViTinh'] = (int)$params['uomioid'][$j];
                $inserts[$params['partnerioid'][$j]]['ODSDonMuaHang'][$i]['DonGia']    = $params['unitprice'][$j]/1000;
                $inserts[$params['partnerioid'][$j]]['ODSDonMuaHang'][$i]['SoLuong']   = $params['qty'][$j];
                $i++;
            }
            $j++;
        }

        //echo '<pre>'; print_r($inserts); die;

        foreach($inserts as $insert)
        {
            if(count($insert) && !$this->isError())
            {
                $service = $this->services->Form->Manual('M401',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    if($ordered)
                    {
                        $form     = new Qss_Model_Form();
                        $form->initData($service->getData(), $params['DeptID']);
                        $service2 = $this->services->Form->Request($form, 2, $params['User'], '');

                        if ($service2->isError())
                        {
                            $this->setError();
                            $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }
                }
            }
        }

        // Kiem tra du item chua
        if(!$this->isError())
        {
            if($allMatch)
            {
                $this->setStatus(1);
            }
            else
            {
                $this->setStatus(0);
            }
        }
    }
}