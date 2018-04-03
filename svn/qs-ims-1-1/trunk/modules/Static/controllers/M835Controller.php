<?php
class Static_M835Controller extends Qss_Lib_Controller
{
    // property
    protected $_model;  /* Remove */

    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $employee   = $this->params->requests->getParam('employee', 0);
        $mWorkorder = new Qss_Model_Maintenance_Workorder();

        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->report = $mWorkorder->analyseEmployeeTasks(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employee
        );
        $this->html->start  = $start;
        $this->html->end    = $end;
    }

}
?>