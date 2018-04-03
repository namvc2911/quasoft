<?php
class Static_M175Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $start     = $this->params->requests->getParam('start', '');
        $end       = $this->params->requests->getParam('end', '');
        $location  = $this->params->requests->getParam('location', 0);
        $group     = $this->params->requests->getParam('group', 0);
        $type      = $this->params->requests->getParam('type', 0);
        $equipment = $this->params->requests->getParam('equipment', 0);
        $mCal      = new Qss_Model_Maintenance_Equip_Calibration();

        $this->html->data = $mCal->getNextCalibrations(
            Qss_Lib_Date::mysqltodisplay($start)
            , Qss_Lib_Date::mysqltodisplay($end)
            , $location
            , $equipment
            , $group
            , $type);
    }
}