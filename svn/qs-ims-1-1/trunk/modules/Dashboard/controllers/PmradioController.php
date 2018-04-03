<?php

class Dashboard_PMRadioController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $mOrder  = new Qss_Model_Maintenance_Workorder();
        $start   = date('Y-m-01');
        $end     = date('Y-m-t');
        $count   = $mOrder->countPMRatio($start, $end);
        $percent = 0;

        if($count)
        {
            $tong    = (int)$count->KeHoach + (int)$count->NgoaiKeHoach;
            $percent = Qss_Lib_Util::formatNumber(($tong?$count->NgoaiKeHoach/$tong:0)*100);
        }

        $this->html->percent = $percent;
        $this->html->outPlan = @(int)$count->NgoaiKeHoach;
    }
}