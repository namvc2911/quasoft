<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M750Controller extends Qss_Lib_Controller
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
		$model = new Qss_Model_Maintenance_Equip_Operation();
		$this->html->equipments = $model->getHoursMeasurePoints();
	}

	public function showAction()
	{
		$operation = new Qss_Model_Maintenance_Equip_Operation();
		$start      = $this->params->requests->getParam('start');
		$end        = $this->params->requests->getParam('end');
		$period 	= $this->params->requests->getParam('period');
		$start = Qss_Lib_Date::displaytomysql($start);
		$end   = Qss_Lib_Date::displaytomysql($end);
		$end = Qss_Lib_Extra::getEndDate($start, $end, $period);
		
		$equipment    = $this->params->requests->getParam('equipment');
		$downtime = $this->_model->getDowntimeStatisticsByPeriod($start,$end,$period,$equipment);
		$runtime = $operation->getRuntimeByPeriod($start,$end,$period,$equipment);
		$arrDowntime = array();
		$arrNumOfDowntime = array();
		$arrRepairTime = array();
		foreach ($downtime as $item)
		{
			switch($period)
			{
				case 'D':
					$id = $item->Ngay;
					break;
				case 'W':
					$id = (string)$item->Tuan.'.'.$item->Nam;
					break;
				case 'M':
					$id = (string)$item->Thang.'.'.$item->Nam;
					break;
				case 'Q':
					$id = (string)$item->Quy.'.'.$item->Nam;
					break;
				case 'Y':
					$id = $item->Nam;
					break;
			}
			$arrDowntime[$id] = $item->ThoiGianDungMay; 
			$arrRepairTime[$id] = $item->ThoiGianXuLy;
			$arrNumOfDowntime[$id] = $item->SoLanDungMay;
		}
		$arrRuntime = array();
		foreach ($runtime as $item)
		{
			switch($period)
			{
				case 'D':
					$id = $item->Ngay;
					break;
				case 'W':
					$id = (string)$item->Tuan.'.'.$item->Nam;
					break;
				case 'M':
					$id = (string)$item->Thang.'.'.$item->Nam;
					break;
				case 'Q':
					$id = (string)$item->Quy.'.'.$item->Nam;
					break;
				case 'Y':
					$id = $item->Nam;
					break;
			}
			$arrRuntime[$id] = $item->SoGio; 
		}
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $period);
		//echo '<pre>';print_r($arrRuntime);die;
		$this->html->time = $aTime;
		$this->html->lich = @$operation->getEquipHoursMeasurePoints($equipment)->SoHoatDongNgay;
		$this->html->arrDowntime = $arrDowntime;
		$this->html->arrRepairTime = $arrRepairTime;
		$this->html->arrNumOfDowntime = $arrNumOfDowntime;
		$this->html->arrRuntime = $arrRuntime;
	}
}

?>