<?php

class Dashboard_PMRadioLastMonthController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mOrder  = new Qss_Model_Maintenance_Workorder();
        $start   = date("Y-n-d", strtotime("first day of previous month"));
        $end     = date("Y-n-d", strtotime("last day of previous month"));
        $count   = $mOrder->countPMRatio($start, $end);
        $percent = 0;

        if($count)
        {
            $tong    = $count->KeHoach + $count->NgoaiKeHoach;
            $percent = Qss_Lib_Util::formatNumber(($tong?$count->NgoaiKeHoach/$tong:0)*100);
        }

        $this->html->percent = $percent;
        $this->html->outPlan = @(int)$count->NgoaiKeHoach;
    }
}