<?php
class Qss_Service_Button_M751_Getmaterials_Save extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['ifid']))
        {
            return;
        }

//        $mOrder      = new Qss_Model_Purchase_Order();
//        $order       = $mOrder->getOrderLineByIFID($params['ifid']);

        $insert   = array();
        $i        = 0;

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['OYeuCauVatTu'][$i]['MaVatTu']       = (int)$params['RefItem'][$i];
                $insert['OYeuCauVatTu'][$i]['TenVatTu']      = (int)$params['RefItem'][$i];
                $insert['OYeuCauVatTu'][$i]['DonViTinh']     = $params['uom'][$i];
                $insert['OYeuCauVatTu'][$i]['SoLuongYeuCau'] = $params['Qty'][$i];
            }
            $i++;
        }

        if(count($insert))
        {
            $service = $this->services->Form->Manual('M751',  (int)$params['ifid'],  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        if(!$this->isError())
        {
            Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$params['ifid'].'&deptid='.$params['deptid'];
        }
    }
}
?>