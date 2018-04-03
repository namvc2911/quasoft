<?php
class Qss_Service_Purchase_Request_SaveLines extends Qss_Lib_Service
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
                $insert['ODSYeuCauMuaSam'][$i]['MaSP']      = (int)$params['RefItem'][$i];
                $insert['ODSYeuCauMuaSam'][$i]['DonViTinh'] = (int)$params['RefUOM'][$i];
                $insert['ODSYeuCauMuaSam'][$i]['SoLuong']   = $params['Qty'][$i];
                $insert['ODSYeuCauMuaSam'][$i]['MaKho']     = @(int)$params['stock'][$i];
            }
            $i++;
        }

//        $removeArr = array();
//        foreach($order as $item)
//        {
//            $removeArr['ODSYeuCauMuaSam'][] = $item->IOID;
//        }


//        if(count($removeArr))
//        {
//            $remove = $this->services->Form->Remove('M412',  (int)$params['ifid'],  $removeArr);
//            if ($remove->isError())
//            {
//                $this->setError();
//                $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }
//        }


        if(count($insert))
        {
            $service = $this->services->Form->Manual('M412',  (int)$params['ifid'],  $insert, false);
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