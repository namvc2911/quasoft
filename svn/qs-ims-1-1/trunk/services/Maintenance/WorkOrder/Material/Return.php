<?php
class Qss_Service_Maintenance_WorkOrder_Material_Return extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['warehouse']))
        {
            return;
        }

        $mInventory = new Qss_Model_Inventory_Inventory();
        $type       = $mInventory->getInputTypeByCode(Qss_Lib_Extra_Const::INPUT_TYPE_RETURN);
        $type       = $type?$type->IOID:0;

        $insert   = array();
        $i        = 0;

        $insert['ONhapKho'][0]['Kho']          = (int)$params['warehouse'];
        $insert['ONhapKho'][0]['LoaiNhapKho']  = (int)$type;
        $insert['ONhapKho'][0]['NgayChungTu']  = date('Y-m-d');

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['ODanhSachNhapKho'][$i]['MaSanPham'] = (int)$params['RefItem'][$i];
                $insert['ODanhSachNhapKho'][$i]['DonViTinh'] = (int)$params['RefUOM'][$i];
                $insert['ODanhSachNhapKho'][$i]['SoLuong']   = $params['Qty'][$i];
            }
            $i++;
        }
        //echo '<pre>'; print_r($insert); die;

        $service = $this->services->Form->Manual('M402',  0,  $insert, false);
        if ($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}
?>