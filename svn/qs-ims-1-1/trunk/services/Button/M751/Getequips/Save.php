<?php
class Qss_Service_Button_M751_Getequips_Save extends Qss_Lib_Service
{


    public function __doExecute ($params)
    {
        if(!isset($params['RefItem']) || !isset($params['ifid']))
        {
            return;
        }

        $mCommon  = new Qss_Model_Extra_Extra();
        $insert   = array();
        $i        = 0;
        $main     = $mCommon->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IFID_M751'=>$params['ifid']));

        foreach ($params['RefItem'] as $itemioid)
        {
            if($params['Qty'][$i] > 0)
            {
                $insert['OYeuCauTrangThietBi'][$i]['Ref_LoaiThietBi']  = (int)$params['RefItem'][$i];
                $insert['OYeuCauTrangThietBi'][$i]['LoaiThietBi']      = $params['Item'][$i];


                $insert['OYeuCauTrangThietBi'][$i]['DonViTinh']     = $params['uom'][$i];
                $insert['OYeuCauTrangThietBi'][$i]['Ref_DonViTinh'] = (int)$params['RefItem'][$i];
                $insert['OYeuCauTrangThietBi'][$i]['SoLuong']       = $params['Qty'][$i];
                $insert['OYeuCauTrangThietBi'][$i]['NgayBatDau']    = $main->NgayBatDau;
                $insert['OYeuCauTrangThietBi'][$i]['NgayKetThuc']   = $main->NgayKetThuc;
            }
            $i++;
        }

        if(count($insert))
        {

            // echo '<pre>'; print_r($insert); die;
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