<?php
class Static_M728Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $start  = $this->params->requests->getParam('start', date('d-m-Y'));
        $end    = $this->params->requests->getParam('end', date('d-m-Y'));
        $mOrder = new Qss_Model_Maintenance_Workorder();

        $this->html->data  = $mOrder->getTrackBreakdown(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end));
        $this->html->start  = $start;
        $this->html->end    = $end;
        $this->html->deptid = $this->_user->user_dept_id;
    }
}