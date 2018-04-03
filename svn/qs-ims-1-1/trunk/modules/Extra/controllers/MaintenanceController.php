<?php

/**
 *
 * @author ThinhTN
 *
 */
class Extra_MaintenanceController extends Qss_Lib_Controller
{

	// Model
	public $_common;
	public $_model;

	// Loai NODE dung cho module "Thong tin thiet bi"
	const NODE_TYPE_NONE        = 'NONE';
	const NODE_TYPE_LOCATION    = 'LOCATION';
	const NODE_TYPE_EQUIP_GROUP = 'EQ_GROUP';
	const NODE_TYPE_EQUIP_TYPE  = 'EQ_TYPE';
	const NODE_TYPE_EQUIP       = 'EQUIP';
	const NODE_TYPE_COMPONENT   = 'COMPONENT';
	const NODE_TYPE_PROJECT     = 'PROJECT';

	/**
	 *
	 * @return unknown_type
	 */
	public function init()
	{
		parent::init();
		// Model
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_model  = new Qss_Model_Extra_Maintenance();

		// Load script (Co ve khong hoat dong)
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');

	}

	// *****************************************************************
	// === FUNCTION: XEM KE HOACH
	// *****************************************************************
	public function createwoAction()
	{
		$locationModel = new Qss_Model_Maintenance_Location();
		$this->html->location = $locationModel->getLocationByCurrentUser();
		
		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_schedule_type', 'day');
		}
		$day        = $this->params->requests->getParam('cday',date('d'));
		$week       = $this->params->requests->getParam('cweek',date('W'));
		$month      = $this->params->requests->getParam('cmonth',date('m'));
		$year       = $this->params->requests->getParam('cyear',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		$assignid   = $this->params->requests->getParam('assignid',0);
		$fid        = $this->params->requests->getParam('fid','');
		$location   = $this->params->requests->getParam('location', 0);

		//echo '<pre>'; print_r($fid); die;

		$this->html->content = $this->views->Maintenance->Plan->Content->Week($fid, $this->_user,$week,$month,$year,$workcenter,$responseid,$assignid,$location);
		$this->html->fid     = $fid;
		$this->html->type     = $type;
		$this->html->day     = $day;
		$this->html->year	= $year;
		$this->html->week = $week;
		$this->html->month	= $month;
	}

	public function pagewoAction()
	{

		$workCenterCode = $this->params->requests->getParam('wc', '');
		$date = $this->params->requests->getParam('date', '');
		$display = $this->params->requests->getParam('display', 0);
		$page = $this->params->requests->getParam('page', 0);
		$totalPage = 0;

		if ($workCenterCode && $date)
		{
			$total = $this->_model->countMaintainPlanByDate(
			Qss_Lib_Date::displaytomysql($date)
			, $workCenterCode);
			$totalPage = $display ? ceil($total / $display) : 0;
		}

		$data = array('total' => $totalPage, 'page' => $page);
		echo Qss_Json::encode(array('error' => 0, 'data' => $data));

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function showwoAction()
	{

        /*
		$workCenterCode = $this->params->requests->getParam('wc', '');
		$date = $this->params->requests->getParam('date', '');
		$display = $this->params->requests->getParam('display', 0);
		$page = $this->params->requests->getParam('page', 0);

		$this->html->notExistWorkOrders = $this->_model->getMaintainPlanByDate(
		Qss_Lib_Date::displaytomysql($date)
		, $workCenterCode
		, $page
		, $display);
		$this->html->wc = $workCenterCode;
		$this->html->date = $date;

		$this->html->workCenters = $this->_model->getWorkCenterByUser('M759', $this->_user);

		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_schedule_type', 'day');
		}
		$day = $this->params->requests->getParam('day',date('d'));
        */
		$week       = $this->params->requests->getParam('cweek',date('W'));
		$month      = $this->params->requests->getParam('cmonth',date('m'));
		$year       = $this->params->requests->getParam('cyear',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		$assignid   = $this->params->requests->getParam('assignid',0);
		$fid        = $this->params->requests->getParam('cfid', '');
		$location   = $this->params->requests->getParam('location', 0);




		$this->html->content = $this->views->Maintenance->Plan->Content->Week($fid, $this->_user,$week,$month,$year,$workcenter,$responseid,$assignid, $location);
		$this->html->fid     = $fid;
	}

	public function deletewoAction()
	{

		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Maintenance->Workorders->Delete($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function savewoAction()
	{

		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Maintenance->Workorders->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	// ***********************************************************************************************
	// === END FUNCTION: XEM KE HOACH
	// ***********************************************************************************************


	// ***********************************************************************************************
	// *** FUNCTION: THONG TIN THIET BI
	// ***********************************************************************************************


	
	/**
	 * END PRIVATE FUNCTION: THONG TIN THIET BI
	 */

}
