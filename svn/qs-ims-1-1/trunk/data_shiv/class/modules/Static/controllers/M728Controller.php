<?php
class Static_M728Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $start   = $this->params->requests->getParam('start', date('d-m-Y'));
        $end     = $this->params->requests->getParam('end', date('d-m-Y'));
        $page    = $this->params->requests->getParam('page', 1);
        $display = $this->params->requests->getParam('display', 5);
        $mOrder  = new Qss_Model_Maintenance_Workorder();
        $total   = $mOrder->countTrackBreakdown(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end));
        $count   = ceil($total/$display);

        $this->html->data  = $mOrder->getTrackBreakdown(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $page
            , $display);
        $this->html->start  = $start;
        $this->html->end    = $end;
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->page   = $page;
        $this->html->display= $display;
        $this->html->count  = $count;
        $this->html->next   = (($page + 1) < $count)?($page + 1):$count;
        $this->html->prev   = (($page - 1) > 1)?($page - 1):1;
    }
}