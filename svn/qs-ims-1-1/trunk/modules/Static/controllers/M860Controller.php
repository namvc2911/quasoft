<?php
class Static_M860Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mRequest = new Qss_Model_Maintenance_Request();

        $start    = Qss_Lib_Date::displaytomysql($this->params->requests->getParam('start'));
        $end      = Qss_Lib_Date::displaytomysql($this->params->requests->getParam('end'));
        $location = $this->params->requests->getParam('location');
        $group    = $this->params->requests->getParam('group');
        $type     = $this->params->requests->getParam('type');
        $equip    = $this->params->requests->getParam('equipment');

        $this->html->report = $mRequest->getRequests($start, $end, $location, $group,  $type, $equip);
    }
}