<?php

/**
 * Class Dashboard_BreakdownMeterController
 * Bieu do dung may thang hien tai
 */
class Dashboard_BreakdownMeterController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mDowntime  = new Qss_Model_Maintenance_Breakdown();
        $mEq        = new Qss_Model_Maintenance_Equip_List();
        $start      = date('Y-m-01');
        $end        = date('Y-m-t');
        $downtime   = $mDowntime->getTotalDowntime($start, $end);
        $totalEq    = $mEq->countEquip($this->_user->user_dept_id);

        $this->html->totalDowntime = $downtime?$downtime:0;
        $this->html->totalEquip    = $totalEq;
    }
}