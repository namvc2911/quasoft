<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M785Controller extends Qss_Lib_Controller
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
	
	public function indexAction()
	{

	}
	/* #Move  */
	public function showAction()
	{
		// Lay cac tham so truyen len
		$start      = $this->params->requests->getParam('start', '');
		$end        = $this->params->requests->getParam('end', '');
		$workcenter = $this->params->requests->getParam('workcenter', '');
		$end        = Qss_Lib_Extra::getEndDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, 'D'
		, Qss_Lib_Extra_Const::$DATE_LIMIT);

		// Lay tong so gio yeu cau theo ke hoach
		$dataSQL = $this->_model->getResourceByDate(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $workcenter);
		$plan = array();
		foreach ($dataSQL as $item)
		{
			$plan[$item->Ngay] = round($item->ThoiGian/60,2); 
		}
		//echo '<pre>';print_r($plan);die;
		$this->html->plan = $plan;
		// Lay tong so gio theo so nhan vien lam viec
		$this->html->emp = $this->getResouceTimeAvailableDataForWorkcenterByDay(
		$start, $end, $workcenter);

		$this->html->date = Qss_Lib_Extra::displayRangeDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), 'D');
	}



	private function getResouceTimePlanDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$countDate = 0;

		$startTmp = date_create($start);
		$endTmp = date_create($end);
		$workArr = array();

		while ($startTmp <= $endTmp)
		{
			if ($countDate == Qss_Lib_Extra_Const::$DATE_LIMIT['D']) // gioi han 60 ngay
			{
				break;
			}
			$countDate++;
			$date = $startTmp->format('Y-m-d');
			$plan = $maint->getMaintPlanWorkTime($date
			, array('workcenter'=>$workcenter, 'group'=>'wc'));

			$workArr[$date] = 0;
				
			foreach ($plan as $item)
			{
				if ($item->Work)
				{
					$workArr[$date] += $item->ThoiGian;
				}
			}
			$startTmp = Qss_Lib_Date::add_date($startTmp, 1); // add start one day
		}
		//echo '<pre>';print_r($workArr);die;
		return $workArr;

	}
	private function getResouceTimeAvailableDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$RefWCal = array();
		$TimeByWC = array();
		$TimeByWCRet = array();

		$start = Qss_Lib_Date::displaytomysql($start);
		$end   = Qss_Lib_Date::displaytomysql($end);

		$maint = new Qss_Model_Extra_Maintenance();
		$empl  = $maint->getWorkingEmplFromWorkCenter($start, $end
		, $workcenter);
		//getWCalByDay
		// Lay ra lich lam viec cua nhan vien
		foreach($empl as $e)
		{
			if($e->RefWCal && !in_array($e->RefWCal, $RefWCal))
			{
				$RefWCal[] = $e->RefWCal;
			}
				
			// Tinh tong cac lich lam viec theo tung don vi
			// dung de xac dinh tung ngay don vi lam bao nhieu tg
			if(!isset($TimeByWC[$e->RefWC][$e->RefWCal]))
			{
				$TimeByWC[$e->RefWC][$e->RefWCal] = 1;
			}
			else
			{
				$TimeByWC[$e->RefWC][$e->RefWCal]++;
			}
		}

		// Lay thoi gian lam viec tung ngay theo lich lam viec
		$WCalByDay = Qss_Lib_Extra::getWCalByDay($RefWCal, $start, $end);
		//		echo '<pre>';
		//		print_r($TimeByWC);
		//		echo '<pre>';
		//		print_r($WCalByDay);
		//		die;

		// Lay tong thoi gian lam viec cua tung to bao tri
		$startTem = date_create($start);
		$endTem   = date_create($end);
		
		while ($startTem <= $endTem)
		{
			$startToDate = $startTem->format('Y-m-d'); // Ngay
			$TimeByWCRet[$startToDate] = 0;
				
			foreach($TimeByWC as $refWC1 => $Cal1)
			{
				foreach($Cal1 as $refWCal1 => $countWCal1)
				{
					if(isset($WCalByDay[$refWCal1][$startToDate]))
					{
						$TimeByWCRet[$startToDate] += $countWCal1
						* $WCalByDay[$refWCal1][$startToDate];
					}
				}
				//$TimeByWCRet
			}
			$startTem = Qss_Lib_Date::add_date($startTem, 1);
		}
		return $TimeByWCRet;
	}
	
}
?>