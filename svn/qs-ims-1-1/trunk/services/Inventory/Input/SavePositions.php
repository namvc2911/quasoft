<?php
class Qss_Service_Inventory_Input_SavePositions extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $mInv   = new Qss_Model_Inventory_Inventory();
        $remove = array();
        $i      = 0;
        $insert = array();
        $ss     = $mInv->getInputStockStatus(@(int)$params['ifid']);

        if(!isset($params['ifid']) || !count($params['ifid']))
        {
            return;
        }

        if(!isset($params['item']))
        {
            return;
        }

        foreach($ss as $item)
        {
            $remove['OThuocTinhChiTiet'][] = $item->IOID;
        }

        if(count($remove))
        {
            $service = $this->services->Form->Remove('M402',  $params['ifid'],  $remove, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        foreach($params['item'] as $item)
        {
            if($params['qty'][$i] > 0)
            {
                $insert['OThuocTinhChiTiet'][$i]['MaSanPham']  = (int)$params['item'][$i];
                $insert['OThuocTinhChiTiet'][$i]['TenSanPham'] = (int)$params['item'][$i];
                $insert['OThuocTinhChiTiet'][$i]['DonViTinh']  = (int)$params['uom'][$i];
                $insert['OThuocTinhChiTiet'][$i]['SoLuong']    =  $params['qty'][$i];
                $insert['OThuocTinhChiTiet'][$i]['SoSerial']   =  $params['serial'][$i];
                $insert['OThuocTinhChiTiet'][$i]['Kho']        = (int)$params['stock'][$i];
                $insert['OThuocTinhChiTiet'][$i]['Bin']        = (int)$params['bin'][$i];
            }
            $i++;
        }

        // echo '<pre>'; print_r($insert); die;
        if(count($insert) && !$this->isError())
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
?>