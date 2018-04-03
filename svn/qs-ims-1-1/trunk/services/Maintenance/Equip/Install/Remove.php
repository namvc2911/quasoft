<?php

class Qss_Service_Maintenance_Equip_Install_Remove extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $mInstall = new Qss_Model_Maintenance_Equip_Install();


        $service =  $this->services->Form->Remove('M173' , (int)$params['ifid'], array());

        if($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }

        if(!$this->isError())
        {
            $last   = $mInstall->getLastInstall((int)$params['equip']);

            if($last)
            {
                $update = array();
                $update['ODanhSachThietBi'][0]['MaKhuVuc']       = @(int)$last->Ref_KhuVuc;
                $update['ODanhSachThietBi'][0]['DayChuyen']      = @(int)$last->Ref_DayChuyen;
                $update['ODanhSachThietBi'][0]['TrungTamChiPhi'] = @(int)$last->Ref_TrungTamChiPhi;
                $update['ODanhSachThietBi'][0]['QuanLy']         = @(int)$last->Ref_NhanVien;

                $service = $this->services->Form->Manual('M705',  (int)$params['equipifid'],  $update, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

        }
    }
}