<?php
class Button_M602Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
    }

    public function serialIndexAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $ifid       = $this->params->requests->getParam('ifid', 0);

        $this->html->data   = $mInventory->getItemConfigByInventory($ifid);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function serialShowAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $mProduct   = new Qss_Model_Extra_Products();
        $ifid       = $this->params->requests->getParam('ifid'  , 0 );
        $auto       = $this->params->requests->getParam('auto'  , 0 );
        $length     = $this->params->requests->getParam('length', 1 );
        $prefix     = $this->params->requests->getParam('prefix', '');
        $reject     = $this->params->requests->getParam('reject', 0 );
        $length     = $length?$length:1; // Tối thiểu serial phai co độ dài 1 ký tự, không có trường hợp = 0

        $this->html->data   = $mInventory->getStockStatusByInventory($ifid, $reject);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->auto   = $auto;
        $this->html->length = $length;
        $this->html->prefix = $prefix;
        $this->html->reject = $reject;
        $this->html->last   = $mProduct->getLastSerial($prefix);
    }

    public function serialSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M602->Serial->Save($params);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}