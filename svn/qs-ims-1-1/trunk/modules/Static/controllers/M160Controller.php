<?php
class Static_M160Controller extends Qss_Lib_Controller
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
        $mList        = new Qss_Model_Maintenance_Equip_List();
        $eqIOID       = $this->params->requests->getParam('equip', 0);
        $locationIOID = $this->params->requests->getParam('location', 0);
        $eqGroupIOID  = $this->params->requests->getParam('group', 0);
        $eqTypeIOID   = $this->params->requests->getParam('type', 0);

        $this->html->report = $mList->getEquipmentsWithItsComponents($locationIOID, $eqGroupIOID, $eqTypeIOID, $eqIOID);

    }
}