<?php
class Static_M707Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function equips2Action()
    {
        $loc     = $this->params->requests->getParam('loc');
        $dat     = new stdClass();
        $mEquip  = new Qss_Model_Maintenance_Equip_List();
        $equips  = $mEquip->getEquipByLocation($loc);
        $i       = 0;

        foreach($equips as $item)
        {
            $dat->{$i}          = new stdClass();
            $dat->{$i}->id      = $item->IOID;
            $dat->{$i}->display = "{$item->MaThietBi} - {$item->TenThietBi} ({$item->MaKhuVuc})";
            $dat->{$i}->parent  = (int)$item->Ref_MaKhuVuc;
            $dat->{$i}->ifid    = (int)$item->IFID_M705;
            $i++;
        }

        echo Qss_Json::encode(array('error' => 0, 'data' => $dat, 'message' => ''));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function addAction()
    {
        $this->html->reasons   = $this->getReasonList();
        $this->html->locations = $this->getLocations();
    }

    public function saveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Breakdown->Add($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
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

    public function getLocations()
    {
        $mLocation = new Qss_Model_Maintenance_Location();
        $locations = $mLocation->getLocations();
        $ret       = array();

        foreach($locations as $item)
        {
            $ret[$item->LocIOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->LocCode} - {$item->LocName}";
        }

        return $ret;
    }

    public function getReasonList()
    {
        $mBreak  = new Qss_Model_Maintenance_Breakdown();
        $reasons = $mBreak->getReasonList();
        $ret     = array();

        foreach($reasons as $item)
        {
            $ret[$item->IOID] = "{$item->Ma} - {$item->Ten}";
        }

        return $ret;
    }

    public function equipsAction()
    {
        $params = $this->params->requests->getParams();
        $mEquip = new Qss_Model_Maintenance_Equip_List();
        $equips = $mEquip->getEquipsByCodeOrName($params['tag']);
        $retval = array();

        foreach ($equips as $item)
        {
            $display  = "{$item->MaThietBi} - {$item->TenThietBi}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
