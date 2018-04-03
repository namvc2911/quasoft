<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M736Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Plan();
	}
	public function indexAction()
	{

	}

	public function showAction()
	{



		$start = $this->params->requests->getParam('start');
		$end = $this->params->requests->getParam('end');
		$location = $this->params->requests->getParam('location');
		$group = $this->params->requests->getParam('group');
		$type = $this->params->requests->getParam('type');
		
		$common = new Qss_Model_Extra_Extra();
		$end    = Qss_Lib_Extra::getEndDate($start, $end, 'D');
		$shift  = $common->getTable(array('*'), 'OCa');

		$this->html->dates = Qss_Lib_Extra::displayRangeDate(
		Qss_Lib_Date::displaytomysql($start),
		$end,
		Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY
		);


		$this->html->data = $this->getPlans(Qss_Lib_Date::displaytomysql($start), $end, $location, $group, $type);
		$this->html->start = date_create($start);
		$this->html->end   = date_create(Qss_Lib_Date::mysqltodisplay($end));
		$this->html->count = count((array) $shift);
		$this->html->shift = $shift;
//
//		$this->html->data = $this->_model->getActivePlans($location,$type,$group);

	}

	public function getPlans($start, $end, $location, $group, $type)
	{

		$mStart =  date_create($start);
		$mEnd   =  date_create($end);
		$mPlan  = new Qss_Model_Maintenance_Plan();
		$print  = array();
		$i      = 0;

//		$plans = $mPlan->getPlansByDate('2016-03-15');
//
//		echo '<pre>'; print_r($plans); die;


		while($mStart <= $mEnd)
		{
			$date  = $mStart->format('Y-m-d');
			$plans = $mPlan->getPlansByDate($date, $location, 0, 0, $group, $type);

			foreach($plans as $item)
			{
				$item->Ref_Ca = (int)$item->Ref_Ca;

				$print[$item->Ref_MaThietBi]['MaThietBi']  = $item->MaThietBi;
				$print[$item->Ref_MaThietBi]['TenThietBi'] = $item->TenThietBi;
				$print[$item->Ref_MaThietBi]['Tick'][$mStart->format('d-m-Y')][$item->Ref_Ca]['Tick']    = 'X';
				$print[$item->Ref_MaThietBi]['Tick'][$mStart->format('d-m-Y')][$item->Ref_Ca]['Title']   = "- ".$item->MoTa.": " ;
				$print[$item->Ref_MaThietBi]['Tick'][$mStart->format('d-m-Y')][$item->Ref_Ca]['Title']  .= $item->MucDoUuTien."\n";
			}

			$mStart = Qss_Lib_Date::add_date($mStart, 1);
		}

		//echo '<pre>'; print_r($print); die;
		return $print;
	}
}

?>