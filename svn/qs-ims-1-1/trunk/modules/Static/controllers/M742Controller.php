<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M742Controller extends Qss_Lib_Controller
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
		$groupby = $this->params->requests->getParam('groupBy');
		$end = Qss_Lib_Extra::getEndDate($start, $end, $period);
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $period);
		$this->html->time = $aTime;
		$this->html->period = $period;
		$this->html->startDate = Qss_Lib_Date::mysqltodisplay($start);
		$this->html->endDate = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->groupBy = $groupby;
		$loc = $this->params->requests->getParam('location');
		$type = $this->params->requests->getParam('type');
		$group = $this->params->requests->getParam('group');
		$filter = array('loc'=>$loc,'eqtype'=>$type,'eqgroup'=>$group);
		$this->html->cost1 = $this->_model->getCostAnalysis($start, $end
		, $period, 1,$filter);
		$this->html->cost2 = $this->_model->getCostAnalysis($start, $end
		, $period, 2,$filter);
		$this->html->cost3 = $this->_model->getCostAnalysis($start, $end
		, $period, 3,$filter);
		//echo '<pre>';print_r($this->html->cost1);die;
	}
}

?>