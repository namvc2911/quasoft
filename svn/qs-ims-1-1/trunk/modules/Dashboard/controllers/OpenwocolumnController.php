<?php

class Dashboard_OpenWOColumnController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mOrder = new Qss_Model_Maintenance_Workorder();
        $mEq    = new Qss_Model_Maintenance_Equip_List();

        $this->html->count      = $mOrder->countWorkOrdersByStatus('', '');
        $this->html->totalEquip = $mEq->countEquip($this->_user->user_dept_id);
    }
}