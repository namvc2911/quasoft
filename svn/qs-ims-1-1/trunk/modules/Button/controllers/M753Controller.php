<?php
class Button_M753Controller extends Qss_Lib_Controller
{
    public function multiIndexAction()
    {
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $mSystem = new Qss_Lib_System();

        $this->html->ifid        = $ifid;
        $this->html->workcenters = $this->getPartners();
        $this->html->equipTypes  = $this->getEquipTypes();
        $this->html->period      = $mSystem->getFieldRegx('OHieuChuanKiemDinh', 'ChuKy');
        $this->html->type        = $mSystem->getFieldRegx('OHieuChuanKiemDinh', 'Loai');
    }

    public function multiSelectAction()
    {
        $filter       = $this->params->requests->getParam('m753_equip_filter', 0);
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $equipType    = $this->params->requests->getParam('equipType', 0);
        $mEquip       = new Qss_Model_Maintenance_Equip_List();
        $mCalibration = new Qss_Model_Maintenance_Equip_Calibration();
        $today        = date('Y-m-d');
        $end          = date('Y-m-d', strtotime($today. ' +7 days'));

        $this->html->filter = $filter;

        if($filter == 'OVERDUE') // Cac thiet bi den han
        {
            $this->html->data = $mCalibration->getNextCalibrations($today, $end);
        }
        elseif($filter == 'EQUIP') // Danh sach thiet bi
        {
            if($equipType)
            {
                $this->html->data = $mEquip->getEquipments(0,0, $equipType);
            }
            else
            {
                $this->html->data = array();
            }
        }
    }

    public function multiShowAction()
    {

    }

    public function multiSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Calibration->Insert($params);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getPartners()
    {
        $mWC     = new Qss_Model_Master_Partner();
        $wcs     = $mWC->getPartners();
        $ret     = array();

        foreach($wcs as $item)
        {
            $ret[$item->IOID] = "{$item->MaDoiTac} - {$item->TenDoiTac}";
        }

        return $ret;
    }

    public function getEquipTypes()
    {
        $mEquip = new Qss_Model_Maintenance_Equipment();
        $types  = $mEquip->getEquipTypes();
        $ret    = array();

        foreach($types as $item)
        {
            if($item->MaLoai)
            {
                $ret[$item->IOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->MaLoai} - {$item->TenLoai}";
            }
            else
            {
                $ret[$item->IOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->TenLoai}";
            }
        }

        return $ret;
    }

    public function componentAction()
    {
        $equipIFID = $this->params->requests->getParam('equipifid', 0);
        $mEquip    = new Qss_Model_Maintenance_Equip_List();
        $component = $mEquip->getComponentsByIFID($equipIFID);
        $retval    = array();

        foreach ($component as $item)
        {
            $display  = str_repeat('&nbsp;', ($item->LEVEL - 1)*3 )."{$item->ViTri} - {$item->BoPhan}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}