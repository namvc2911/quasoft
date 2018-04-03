<?php
class Static_M825Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri(); //lay crurl (giong data) de su dung trong html
    }

    public function indexAction()
    {
        $this->html->suppliers = $this->getSuppliers();
    }

    public function showAction()
    {
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $supplier   = $this->params->requests->getParam('supplier', '');
        $mOrder     = new Qss_Model_Purchase_Order();

        $this->html->report = $mOrder->getHireOrders(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $supplier
        );
    }

    public function getSuppliers()
    {
        $mPartner = new Qss_Model_Master_Partner();
        $types    = $mPartner->getSuppliers();
        $ret      = array();

        foreach($types as $item)
        {
            $ret[$item->IOID] = $item->MaDoiTac . ' - ' . $item->TenDoiTac;
        }

        return $ret;
    }


}