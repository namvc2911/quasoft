<?php
class Qss_Service_Purchase_Order_SaveLines extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['ifid']))
        {
            return;
        }

        $mOrder      = new Qss_Model_Purchase_Order();
        $order       = $mOrder->getOrderLineByIFID($params['ifid']);

        $insert   = array();
        $i        = 0;

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['ODSDonMuaHang'][$i]['MaSP']      = (int)$params['RefItem'][$i];
                $insert['ODSDonMuaHang'][$i]['SoYeuCau']  = (int)$params['RefRequest'][$i];
                $insert['ODSDonMuaHang'][$i]['DonViTinh'] = (int)$params['RefUOM'][$i];
                $insert['ODSDonMuaHang'][$i]['SoLuong']   = $params['Qty'][$i];
            }
            $i++;
        }

        $removeArr = array();
        foreach($order as $item)
        {
            $removeArr['ODSDonMuaHang'][] = $item->IOID;
        }


        if(count($removeArr))
        {
            $remove = $this->services->Form->Remove('M401',  (int)$params['ifid'],  $removeArr);
            if ($remove->isError())
            {
                $this->setError();
                $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }


        if(count($insert))
        {
            $service = $this->services->Form->Manual('M401',  (int)$params['ifid'],  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        if(!$this->isError())
        {
            $this->setRedirect('/user/form/edit?ifid='.$params['ifid'].'&deptid='.$params['deptid']);
        }
    }
}
?>