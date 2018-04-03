<?php
class Button_M705Controller extends Qss_Lib_Controller
{
    public function copyIndexAction()
    {
        $ifid  = $this->params->requests->getParam('ifid', 0);
        $mList = new Qss_Model_Maintenance_Equip_List();

        $this->html->components = $mList->getComponentsByIFID($ifid);
    }

    // Copy thiet bi
    public function copyEquipIndexAction()
    {
        $this->html->ifid  = $this->params->requests->getParam('ifid', 0);
    }

    public function copyEquipSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Equip->Duplicate($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    // Copy cau truc thiet bi
    public function copyComponentIndexAction()
    {
        $this->html->ifid  = $this->params->requests->getParam('ifid', 0);
    }

    public function copyComponentShowAction()
    {
        $equipIFID  = $this->params->requests->getParam('equip_ifid', 0);
        $ifid       = $this->params->requests->getParam('ifid', 0);
        $mList      = new Qss_Model_Maintenance_Equip_List();

        if($equipIFID != $ifid)
        {
            $this->html->components = $mList->getComponentsByIFID($equipIFID);
        }
        else
        {
            $this->html->components = array();
        }
    }

    public function copyComponentSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Equip->CopyComponent($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>