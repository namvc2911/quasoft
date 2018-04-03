<?php

class Dashboard_OpenWOPieController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mOrder = new Qss_Model_Maintenance_Workorder();
        $this->html->count = $mOrder->countWorkOrdersByStatus('', '');
    }
}