<?php

class Dashboard_BreakdownMeterLastMonthController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mDowntime  = new Qss_Model_Maintenance_Breakdown();
        $mEq        = new Qss_Model_Maintenance_Equip_List();
        $start      = date("Y-n-d", strtotime("first day of previous month"));
        $end        = date("Y-n-d", strtotime("last day of previous month"));
        $downtime   = $mDowntime->getTotalDowntime($start, $end);
        $totalEq    = $mEq->countEquip($this->_user->user_dept_id);

        $this->html->totalDowntime = $downtime?$downtime:0;
        $this->html->totalEquip    = $totalEq;
    }
}