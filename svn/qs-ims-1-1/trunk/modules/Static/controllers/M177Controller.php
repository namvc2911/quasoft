<?php
class Static_M177Controller extends Qss_Lib_Controller
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
        $mCalibration   = new Qss_Model_Maintenance_Equip_Calibration();
        $locationIOID   = $this->params->requests->getParam('location', 0);
        $equipTypeIOID  = $this->params->requests->getParam('type', 0);
        $equipGroupIOID = $this->params->requests->getParam('group', 0);
        $equipIOID      = $this->params->requests->getParam('equipment', 0);

        $this->html->equips = $mCalibration->getEquips($locationIOID, $equipTypeIOID, $equipGroupIOID, $equipIOID);
    }
}