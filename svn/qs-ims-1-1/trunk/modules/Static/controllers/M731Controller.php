<?php
/**
 * Class Static_M731Controller
 */
class Static_M731Controller extends Qss_Lib_Controller
{
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
		$mPlan              = new Qss_Model_Maintenance_Plan();
		$group              = $this->params->requests->getParam('group');
		$type               = $this->params->requests->getParam('type');
		$location           = $this->params->requests->getParam('location');
		$maintType          = $this->params->requests->getParam('maint_type', 0);
		$maintPriority		= $this->params->requests->getParam('maint_priority', 0);
		$this->html->report	= $mPlan->getMaintainStatus($location,$type,$group,$maintPriority);
	}
}

?>