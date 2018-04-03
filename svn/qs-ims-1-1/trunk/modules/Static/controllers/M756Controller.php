<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M756Controller extends Qss_Lib_Controller
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
		$this->_model     = new Qss_Model_Maintenance_Workorder();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	
/**
	 * Lịch sử dùng loại vật tư nào đó theo thời điểm nào và cho thiết bị gì, số
	 * lượng bao nhiêu.
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

		$this->html->report = $this->_model->getMaterials(
		0
		, 0
		, Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, 0
		, array()
		, $locationIOID
		, $eqTypeIOID
		, $eqGroupIOID
		,$materialIOID);

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