<?php

/**
  b
 */
class Report_FlowController extends Qss_Lib_Controller
{

	public function init()
	{
		// $this->i_SecurityLevel = 15;
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
	}

	public function indexAction()
	{
		
	}
	
	public function luuluongAvgbyyearAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
	}
	
	public function luuluongAvgbyyear1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$startYear      = $this->params->requests->getParam('start');
		$endYear        = $this->params->requests->getParam('end');
		
		
		$this->html->luuluong    = $flowModel->getAvgLuuLuongByYear($workstation, $startYear, $endYear);
		$this->html->start       = $startYear;
		$this->html->end         = $endYear;
		$this->html->workstation = $workstationStr;
	}
	
	public function mucnuocAvgbyyearAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
		
	}	
	
	public function mucnuocAvgbyyear1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$startYear      = $this->params->requests->getParam('start');
		$endYear        = $this->params->requests->getParam('end');
		
		
		$this->html->luuluong    = $flowModel->getAvgMucNuocByYear($workstation, $startYear, $endYear);
		$this->html->start       = $startYear;
		$this->html->end         = $endYear;
		$this->html->workstation = $workstationStr;
	}	
	
	
	public function luongmuaAvgbyyearAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
		
	}	
	
	public function luongmuaAvgbyyear1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$startYear      = $this->params->requests->getParam('start');
		$endYear        = $this->params->requests->getParam('end');
		
		
		$this->html->luuluong    = $flowModel->getAvgLuongMuaByYear($workstation, $startYear, $endYear);
		$this->html->start       = $startYear;
		$this->html->end         = $endYear;
		$this->html->workstation = $workstationStr;
	}	
	public function luuluongAvgbydayAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
	}
	
	public function luuluongAvgbyday1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$month      = $this->params->requests->getParam('month');
		$year        = $this->params->requests->getParam('year');
		
		$this->html->luuluong    = $flowModel->getAvgLuuLuongByDay($workstation, $month, $year);
		$this->html->month       = $month;
		$this->html->year		= $year;
		$this->html->workstation = $workstationStr;
	}
	
	public function mucnuocAvgbydayAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
		
	}	
	
	public function mucnuocAvgbyday1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$month      = $this->params->requests->getParam('month');
		$year        = $this->params->requests->getParam('year');
				
		$this->html->luuluong    = $flowModel->getAvgMucNuocByDay($workstation, $month, $year);
		$this->html->month       = $month;
		$this->html->year		= $year;
		$this->html->workstation = $workstationStr;
	}	
	
	
	public function luongmuaAvgbydayAction()
	{
		$commonModel = new Qss_Model_Extra_Extra();
		$this->html->workstation = $commonModel->getTable(array('*'), 'Tram' );
		
	}	
	
	public function luongmuaAvgbyday1Action()
	{
		$flowModel = new Qss_Model_Extra_Flow();
		$workstation    = $this->params->requests->getParam('workstation');
		$workstationStr = $this->params->requests->getParam('workstationStr');
		$month      = $this->params->requests->getParam('month');
		$year        = $this->params->requests->getParam('year');
		
		
		$this->html->luuluong    = $flowModel->getAvgLuongMuaByDay($workstation, $month, $year);
		$this->html->month       = $month;
		$this->html->year		= $year;
		$this->html->workstation = $workstationStr;
	}		
	

}
