<?php

/**
 *
 * @author: ThinhTuan
 * @desc: Bao cao vat tu theo thiet bi
 */
class Static_M738Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Equipment();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	

	/**
	 * Báo cáo truy vấn vật tư đang được sử dụng ở thiết bị nào
	 */
	public function indexAction()
	{

	}

	public function showAction()
	{
		$start        = $this->params->requests->getParam('start', '');
		$end          = $this->params->requests->getParam('end', '');
		$materialIOID = $this->params->requests->getParam('material', 0);
		$locationIOID = $this->params->requests->getParam('location', 0);
		$eqGroupIOID  = $this->params->requests->getParam('group', 0);
		$eqTypeIOID   = $this->params->requests->getParam('type', 0);

		$equipModel         = new Qss_Model_Maintenance_Equipment();
		$this->html->report = $equipModel->getMaterials(
		$materialIOID
		, $locationIOID
		, $eqTypeIOID
		, $eqGroupIOID);

		$this->html->start         = $start;
		$this->html->end           = $end;
		$this->html->locationIOID  = $locationIOID;
		$this->html->location      = $this->params->requests->getParam('locationStr', '');
		$this->html->eqGroupIOID   = $eqGroupIOID;
		$this->html->eqGroup       = $this->params->requests->getParam('groupStr', '');
		$this->html->eqTypeIOID    = $eqTypeIOID;
		$this->html->eqType        = $this->params->requests->getParam('typeStr', '');
		$this->html->materialIOID  = $materialIOID;
		$this->html->material      = $this->params->requests->getParam('materialStr', '');
	}
	
}

?>