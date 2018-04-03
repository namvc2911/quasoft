<?php
class Static_M176Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->html->employees = $this->getEmployees();
        $this->html->status    = $this->getTaskStatus();
    }

    public function showAction()
    {
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $employee   = $this->params->requests->getParam('employee', 0);
        $status     = $this->params->requests->getParam('status', '');
        $status     = $status?(int)preg_replace('/A/', '', $status):'';
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $page       = $this->params->requests->getParam('einfo_history_page', 1);
        $display    = $this->params->requests->getParam('einfo_history_display', 20);
        $total      = $mWorkorder->countEmployeeTasks(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employee
            , $status
        );
        $total           = $total?$total->Total:0;
        $cpage           = ceil($total / $display);

        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->tasks  = $mWorkorder->getEmployeeTasks(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employee
            , $page
            , $display
            , $status
        );
        $this->html->page      = $page;
        $this->html->display   = $display;
        $this->html->totalPage = $cpage?$cpage:1;
        $this->html->next      = ($page < $cpage)?($page + 1):$cpage;
        $this->html->back      = ($page > 1)?($page - 1):1;
        $this->html->sttAdd    = ($page - 1) * $display;
    }

    public function getEmployees()
    {
        $common  = new Qss_Model_Extra_Extra();
        $emps    = $common->getTable(array('*'), 'ODanhSachNhanVien', array(), array('TenNhanVien'));
        $ret     = array();

        foreach($emps as $item)
        {
            $ret[$item->IOID] = "{$item->MaNhanVien} - {$item->TenNhanVien}";
        }

        return $ret;
    }

    public function getTaskStatus()
    {
        $task    = Qss_Lib_System::getFieldRegx('OCongViecBTPBT', 'ThucHien');
        $ret     = array();

        foreach($task as $key=>$value)
        {
            $keyTmp       = "A".$key;
            $ret[$keyTmp] = "{$value}";
        }

        return $ret;
    }
}