<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M782Controller extends Qss_Lib_Controller
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
	
	/* #Move  */
	public function indexAction()
	{

	}
	/* #Move  */
	public function showAction()
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$start = $this->params->requests->getParam('start', '');
		$end = $this->params->requests->getParam('end', '');
		$refWorkcenter = $this->params->requests->getParam('workcenter', 0);
		$emplTime = $maint->getWorkingHourByEmployee(Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), $refWorkcenter);
		$empl = $maint->resourceEmpReportGetEmployee($refWorkcenter);
		$eTime = array();
		//$this->_common->showTableStucture('OCongViecBaoTri');
		foreach ($emplTime as $et)
		{
			if ($et->Ref_Worker && $et->Time)
			{
				$eTime[$et->Ref_Worker]['Work'][$et->Ref_Work] = $et->Time;

                if(!isset($eTime[$et->Ref_Worker]['Detail']))
                {
                    $eTime[$et->Ref_Worker]['Detail'] = $et->CongViec;
                }
                else
                {
                    $eTime[$et->Ref_Worker]['Detail'] .= $et->CongViec;
                }

			}
		}

		foreach ($empl as $e)
		{
			$eTime[$e->IOID]['Group']['ID'] = @(int) $e->DVIOID;
			$eTime[$e->IOID]['Group']['Code'] = $e->Ma;
			$eTime[$e->IOID]['Group']['Name'] = $e->Ten;
			$eTime[$e->IOID]['Emp']['Code'] = $e->MaNhanVien;
			$eTime[$e->IOID]['Emp']['Name'] = $e->TenNhanVien;
		}


		//echo '<pre>'; print_r($eTime); die;

		$this->html->works = $this->getMaintWork();
		$this->html->report = $eTime;
		$this->html->start = $start;
		$this->html->end = $end;

	}
	private function getMaintWork()
	{
//		$common = new Qss_Model_Extra_Extra();
//		$filter['module'] = 'OCongViecBaoTri';
//		$works = $common->getDataset($filter);
        $mTable = Qss_Model_Db::Table('OCongViecBaoTri');
		$retval = array();

		foreach ($mTable->fetchAll() as $w)
		{
			$retval[$w->IOID] = $w;
		}
		return $retval;

	}
}
?>