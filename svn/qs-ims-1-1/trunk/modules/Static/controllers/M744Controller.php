<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M744Controller extends Qss_Lib_Controller
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
		$this->_model     = new Qss_Model_Maintenance_Breakdown();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	public function indexAction()
	{

	}

	public function showAction()
	{
		$breakdownModel = new Qss_Model_Maintenance_Breakdown();
		$locIOID        = $this->params->requests->getParam('location', 0);
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$period         = $this->params->requests->getParam('period', 'D');
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');
		$startMysql     = Qss_Lib_Date::displaytomysql($start);
		$endMysql       = Qss_Lib_Date::displaytomysql($end);
		$end            = Qss_Lib_Extra::getEndDate($startMysql, $endMysql, $period);

		$this->html->period    = $period;
		$this->html->startDate = $start;
		$this->html->endDate   = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->time      = Qss_Lib_Extra::displayRangeDate($startMysql, $end, $period);
		$this->html->breakdown = $breakdownModel->getDowntimeStatisticsByPeriod(
		$startMysql, $endMysql, $period, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID);
	}
	
}

?>