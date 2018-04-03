<?php
class Qss_Service_Inventory_Output_SaveLines extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['ifid']))
        {
            return;
        }

        $mInventory  = new Qss_Model_Inventory_Inventory();
        $output = $mInventory->getOutputLineByOuputIFID($params['ifid']);
        $insert   = array();
        $i        = 0;

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['ODanhSachXuatKho'][$i]['MaSP']      = (int)$params['RefItem'][$i];
                $insert['ODanhSachXuatKho'][$i]['DonViTinh'] = (int)$params['RefUOM'][$i];
                $insert['ODanhSachXuatKho'][$i]['SoLuong']   = $params['Qty'][$i];
            }
            $i++;
        }

        $removeArr = array();
        foreach($output as $item)
        {
            $removeArr['ODanhSachXuatKho'][] = $item->IOID;
        }

        if(count($removeArr))
        {
            $remove = $this->services->Form->Remove('M506',  (int)$params['ifid'],  $removeArr);
            if ($remove->isError())
            {
                $this->setError();
                $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }            
        }


        if(count($insert))
        {
            $service = $this->services->Form->Manual('M506',  (int)$params['ifid'],  $insert, false);
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