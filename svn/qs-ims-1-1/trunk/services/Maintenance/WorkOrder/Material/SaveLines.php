<?php
class Qss_Service_Maintenance_WorkOrder_Material_SaveLines extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['ifid']))
        {
            return;
        }

        $insert   = array();
        $i        = 0;

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['OVatTuPBT'][$i]['Ngay']            = date('Y-m-d');
                $insert['OVatTuPBT'][$i]['MaVatTu']         = (int)$params['RefItem'][$i];
                $insert['OVatTuPBT'][$i]['DonViTinh']       = (int)$params['RefUOM'][$i];
                $insert['OVatTuPBT'][$i]['SoLuong']         = $params['Qty'][$i];
                $insert['OVatTuPBT'][$i]['SoLuongDuKien']   = $params['Qty'][$i];
            }
            $i++;
        }

        if(count($insert))
        {
            $service = $this->services->Form->Manual('M759',  (int)$params['ifid'],  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }
    }
}
?>