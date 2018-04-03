<?php
class Static_M748Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	
	public function indexAction()
	{

	}

	public function showAction()
	{
	    $model        = new Qss_Model_Maintenance_Plan();
		$equipIOID    = $this->params->requests->getParam('equipment', 0);
		$locationIOID = $this->params->requests->getParam('location', 0);
		$eqGroupIOID  = $this->params->requests->getParam('group', 0);
		$eqTypeIOID   = $this->params->requests->getParam('type', 0);
		$planWorks    = $model->getPlanConfigs($locationIOID, $eqTypeIOID, $eqGroupIOID, $equipIOID);
		$retval       = array();
		$oldCongViec  = '';
		$oldIFID      = '';

		foreach ($planWorks as $item) {
            if($item->IFID != $oldIFID || $item->MoTaCongViec != $oldCongViec) {
                $retval[] = $item;
            }

            $oldIFID     = $item->IFID;
            $oldCongViec = $item->MoTaCongViec;
        }

		$this->html->report       = $retval;
		$this->html->equipIOID    = $equipIOID;
		$this->html->equip        = $this->params->requests->getParam('equipmentStr', '');
		$this->html->locationIOID = $locationIOID;
		$this->html->location     = $this->params->requests->getParam('locationStr', '');
		$this->html->eqGroupIOID  = $eqGroupIOID;
		$this->html->eqGroup      = $this->params->requests->getParam('groupStr', '');
		$this->html->eqTypeIOID   = $eqTypeIOID;
		$this->html->eqType       = $this->params->requests->getParam('typeStr', '');
	}
}

?>