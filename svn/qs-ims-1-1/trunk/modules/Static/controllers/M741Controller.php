<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M741Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Workorder();
	}
    public function indexAction()
	{

	}

	public function showAction()
	{
		$start = Qss_Lib_Date::displaytomysql($this->params->requests->getParam('start'));
		$end = Qss_Lib_Date::displaytomysql($this->params->requests->getParam('end'));
		$period = $this->params->requests->getParam('period');
		
		$this->html->start = $start;
		$this->html->end = $end;
		$this->html->period = $period;
		$group = $this->params->requests->getParam('group');
		$equipment = $this->params->requests->getParam('equipment');
		$location = $this->params->requests->getParam('location');
		$end = Qss_Lib_Extra::getEndDate($start, $end, $period);
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $period); // Range time
		$type = $this->params->requests->getParam('type');
		$this->html->time = $aTime;
		$this->html->cost = $this->_model->getCostByPeriod(
					$period
					, $start
					, $end
					, $type
					, $group
					, $equipment
					, $location);
		$this->html->currency = Qss_Lib_Extra::getDefaultCurrency();
	
	}
}

?>