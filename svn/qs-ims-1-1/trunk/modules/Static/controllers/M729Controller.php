<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M729Controller extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Maintenance_Calendar();

		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/extra/maintenance/calendar.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts-more.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/themes/grid.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/extra/static/m729.js');
		$this->headLink($this->params->requests->getBasePath() . '/css/calendar.css');
	}
	public function indexAction ()
	{
		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_maintenance_type', 'week');
		}
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		$assignid = $this->params->requests->getParam('assignid',0);
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Day($this->_user,$day,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'week':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Week($this->_user,$week,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'month':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Month($this->_user,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'year':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Year($this->_user,$year,$workcenter,$responseid,$assignid);
				break;
		}
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->type = $type;
		$this->html->workcenter = $workcenter;
		$this->html->responseid = $responseid;
		$this->html->assignid = $assignid;
        $this->html->steps  = Qss_Lib_System::getStepsByForm('M759');
		$model = new Qss_Model_Extra_Extra();
		$this->html->workcenters = $model->getTable(array('*'),'ODonViSanXuat');
		$this->html->employees = $model->getTable(array('*'),'ODanhSachNhanVien');

	}
	public function reloadAction ()
	{
		$type = $this->params->requests->getParam('type','day');
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		//echo $week;die;
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Day($this->_user,$day,$month,$year,$workcenter,$responseid);
				break;
			case 'week':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Week($this->_user,$week,$month,$year,$workcenter,$responseid);
				break;
			case 'month':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Month($this->_user,$month,$year,$workcenter,$responseid);
				break;
			case 'year':
				$this->html->select = $this->views->Maintenance->Calendar->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Maintenance->Calendar->Content->Year($this->_user,$year,$workcenter,$responseid);
				break;
		}
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->workcenter = $workcenter;
		$this->html->responseid = $responseid;
		$this->params->cookies->set('cal_maintenance_type', $type);
		$model = new Qss_Model_Extra_Extra();
		$this->html->workcenters = $model->getTable(array('*'),'ODonViSanXuat');
		$this->html->employees = $model->getTable(array('*'),'ODanhSachNhanVien');
	}
	public function scheduleAction ()
	{
		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_schedule_type', 'day');
		}
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		$assignid = $this->params->requests->getParam('assignid',0);
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Day($this->_user,$day,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'week':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Week($this->_user,$week,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'month':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Month($this->_user,$month,$year,$workcenter,$responseid,$assignid);
				break;
			case 'year':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Year($this->_user,$year,$workcenter,$responseid,$assignid);
				break;
		}
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->type = $type;
		$this->html->workcenter = $workcenter;
		$this->html->responseid = $responseid;
		$this->html->assignid = $assignid;
		$model = new Qss_Model_Extra_Extra();
		$this->html->workcenters = $model->getTable(array('*'),'ODonViSanXuat');
		$this->html->employees = $model->getTable(array('*'),'ODanhSachNhanVien');
	}
	public function scheduleReloadAction ()
	{
		$type = $this->params->requests->getParam('type','day');
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		//echo $week;die;
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$responseid = $this->params->requests->getParam('responseid',0);
		switch($type)
		{
			case 'day':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Day($this->_user,$day,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Day($this->_user,$day,$month,$year,$workcenter,$responseid);
				break;
			case 'week':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Week($this->_user,$week,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Week($this->_user,$week,$month,$year,$workcenter,$responseid);
				break;
			case 'month':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Month($this->_user,$month,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Month($this->_user,$month,$year,$workcenter,$responseid);
				break;
			case 'year':
				$this->html->select = $this->views->Maintenance->Schedule->Select->Year($this->_user,$year);
				$this->html->content = $this->views->Maintenance->Schedule->Content->Year($this->_user,$year,$workcenter,$responseid);
				break;
		}
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->workcenter = $workcenter;
		$this->html->responseid = $responseid;
		$this->params->cookies->set('cal_maintenance_type', $type);
		$model = new Qss_Model_Extra_Extra();
		$this->html->workcenters = $model->getTable(array('*'),'ODonViSanXuat');
		$this->html->employees = $model->getTable(array('*'),'ODanhSachNhanVien');
	}
	public function createAction ()
	{

	}

	/**
	 * Save plan work orders
	 */
	public function saveplanworkorderAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Maintenance->Calendar->Saveplanworkorder($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Save work orders
	 */
	public function saveworkorderAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Maintenance->Calendar->Saveworkorder($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}



	/**
	 * Hiển thị thông tin phiêu bảo trì (hoặc phiếu bảo trì kế hoạch)
	 */
	public function editAction()
	{
		$common            = new Qss_Model_Extra_Extra();
		$maintenanceModel  = new Qss_Model_Extra_Maintenance();
		$ifid              = $this->params->requests->getParam('ifid', 0);
		$module            = $this->params->requests->getParam('module', 0);
		$ifidField         = 'IFID_'.trim($module);
		$wo                = $common->getTable
		(array('*'), 'OPhieuBaoTri', array($ifidField=>$ifid), array(), 'NO_LIMIT', 1);
		$iForm             = $common->getTable(array('Status'), 'qsiforms', array('IFID'=>$ifid), array(), 'NO_LIMIT', 1);
		$updatePermission  = in_array(@(int)$iForm->Status, array(3, 4, 5))?false:true;
			
		$this->html->module    = $module;
		$this->html->ifid      = $ifid;
		$this->html->wo        = $wo;
		$this->html->update    = $updatePermission;
	}

	public function tasksAction()
	{
		$common            = new Qss_Model_Extra_Extra();
		$maintenanceModel  = new Qss_Model_Extra_Maintenance();
		$ifid              = $this->params->requests->getParam('ifid', 0);
		$module            = $this->params->requests->getParam('module', 0);
		$ifidField         = 'IFID_'.trim($module);
		$tasks             = $maintenanceModel->getTasksOfOPhieuBaoTri($module, $ifid);
		$wo                = $common->getTable
		(array('*'), 'OPhieuBaoTri', array($ifidField=>$ifid), array(), 'NO_LIMIT', 1);


		$tempNgayBatDau    = $wo->NgayBatDau?$wo->NgayBatDau:$wo->NgayYeuCau;
		$dNgayBatDau       = date_create($tempNgayBatDau);
		$dNgayKetThuc      = Qss_Lib_Date::add_date($dNgayBatDau, 2);
		$tempNgayKetThuc   = $wo->Ngay?$wo->Ngay:$dNgayKetThuc->format('Y-m-d');
		$iForm             = $common->getTable(array('Status'), 'qsiforms', array('IFID'=>$ifid), array(), 'NO_LIMIT', 1);
		$updatePermission  = in_array(@(int)$iForm->Status, array(3, 4, 5))?false:true;

		// Luu y: Ngay ket thuc chi duoc chon tu cong viec khi khong ton tai ngay ket thuc
		foreach($tasks as $task)
		{
			if(!$wo->Ngay
			&& $task->Ngay
			&& Qss_Lib_Date::compareTwoDate($tempNgayKetThuc, $task->Ngay) == -1)
			{
				$tempNgayKetThuc = $task->Ngay;
			}
				
			// Set ngay bat dau cau cong viec bang ngay bat dau phieu neu cong viec ko co ngay bat dau
			if(!$task->Ngay)
			{
				$task->Ngay = $tempNgayBatDau;
			}
		}
		$this->html->module    = $module;
		$this->html->ifid      = $ifid;
		$this->html->wo        = $wo;
		$this->html->tasks     = $tasks;
		$this->html->startDate = $tempNgayBatDau;
		$this->html->endDate   = $tempNgayKetThuc;
		$this->html->update    = $updatePermission;
	}

	public function removeAction()
	{
        $params = $this->params->requests->getParams();
        $user   = Qss_Register::get('userinfo');
        $ifid   = isset($params['workorder'])?$params['workorder']:array();
        $deptid = array();

        foreach ($ifid as $item)
        {
            $deptid[] = $user->user_dept_id;
        }

        $service = $this->services->Form->Delete($ifid, $deptid);
        echo $service->getMessage();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
	}

	public function statusAction()
	{
		$params  = $this->params->requests->getParams();
		$ifids   = isset($params['workorder'])?$params['workorder']:array();
		$stepno  = isset($params['stepno'])?$params['stepno']:0;
		$user    = Qss_Register::get('userinfo');
		$comment = '';

		$deptids = array();

		foreach($ifids as $ifid)
		{
			$deptids[] = $user->user_dept_id;
		}

		foreach ($ifids as $key=>$ifid)
		{
			$deptid = $deptids[$key];
			$form   = new Qss_Model_Form();
			$form->initData($ifid, $deptid);
			$check  = $this->b_fCheckRightsOnForm($form,4);

			if($check)
			{
				$service = $this->services->Form->Request($form, $stepno, $user, $comment);
			}
		}

		if($check && count($ifids) > 1)
		{
			$service->setError(false);
		}
		if($check)
		{
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function adjustAction()
	{
		$ifid = $this->params->requests->getParam('ifid');
		$date = $this->params->requests->getParam('date');
		$sdate = $this->params->requests->getParam('sdate');
		$edate = $this->params->requests->getParam('edate');
		$responseid = (int)$this->params->requests->getParam('responseid');
		$dvbtid = (int)$this->params->requests->getParam('dvbtid');
		if(Qss_Lib_Date::compareTwoDate($sdate, $edate)==1)
		{
			$edate = $date;
		}
		else 
		{
			$diff = Qss_Lib_Date::divDate( $edate,$sdate);
			$enddate = Qss_Lib_Date::add_date(date_create($date), $diff);
			$edate = date_format($enddate,'Y-m-d');
		}
		$arrUpdate = array('OPhieuBaoTri'=>array(0=>array('NgayBatDauDuKien'=>$date,'NgayDuKienHoanThanh'=>$edate,/*'Ngay'=>$edate,*/'NguoiThucHien'=>$responseid,'MaDVBT'=>$dvbtid)));
		$service = $this->services->Form->Manual('M759',$ifid,$arrUpdate);
		if(!$service->isError())
		{
			$this->_model->adjustWorkOrder($ifid);
		}
		//echo Qss_Json::encode(array('error'=>false));
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function materialAction()
	{
		$type  = $this->params->requests->getParam('type','week');
		$day   = $this->params->requests->getParam('day',date('d'));
		$week  = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year  = $this->params->requests->getParam('year',date('Y'));

		$mOrder    = new Qss_Model_Maintenance_Workorder();
		$sonar     = new Qss_Model_Calendar_Solar();
		$startdate = '';
		$enddate   = '';

		switch($type)
		{
			case 'day':
				$startdate = sprintf('%3$s-%2$s-%1$s',str_pad($day, 2, '0', STR_PAD_LEFT),str_pad($month, 2, '0', STR_PAD_LEFT),$year);
				$enddate   = sprintf('%3$s-%2$s-%1$s',str_pad($day, 2, '0', STR_PAD_LEFT),str_pad($month, 2, '0', STR_PAD_LEFT),$year);
			break;

			case 'week':
				if($week == 53 && $month == 1)
				{
					$year--;
				}

				$startd    = Qss_Lib_Date::getDateByWeek($week,$year);
				$startdate = $startd->format('Y-m-d');
				$enddate   = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');
			break;

			case 'month':

				$a           = $sonar->adjustDate($month, $year);
				$monthTemp   = $a[0];
				$yearTemp    = $a[1];
				$daysInMonth = $sonar->getDaysInMonth($monthTemp, $yearTemp);

				$startdate = sprintf('%3$s-%2$s-%1$s', '01' ,str_pad($month, 2, '0', STR_PAD_LEFT),$year);
				$enddate   = sprintf('%3$s-%2$s-%1$s', $daysInMonth ,str_pad($month, 2, '0', STR_PAD_LEFT),$year);
			break;

			case 'year':
				$daysInMonth = $sonar->getDaysInMonth(12, $year);
				$startdate   = sprintf('%3$s-%2$s-%1$s','01','01',$year);
				$enddate     = sprintf('%3$s-%2$s-%1$s',$daysInMonth,12,$year);
			break;
		}

		$this->html->material = $mOrder->getRequiredMaterialsInRangeDate($startdate, $enddate);
	}

	public function paramAction()
	{

		$start = $this->params->requests->getParam('start', '');
		$end   = $this->params->requests->getParam('end', '');
		$type  = $this->params->requests->getParam('type','week');
		$day   = $this->params->requests->getParam('day',date('d'));
		$week  = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year  = $this->params->requests->getParam('year',date('Y'));

		$mOrder    = new Qss_Model_Maintenance_Workorder();
		$sonar     = new Qss_Model_Calendar_Solar();
		$startdate = '';
		$enddate   = '';

		switch($type)
		{
			case 'day':
				$startdate = sprintf('%3$s-%2$s-%1$s',str_pad($day, 2, '0', STR_PAD_LEFT),str_pad($month, 2, '0', STR_PAD_LEFT),$year);
				$enddate   = sprintf('%3$s-%2$s-%1$s',str_pad($day, 2, '0', STR_PAD_LEFT),str_pad($month, 2, '0', STR_PAD_LEFT),$year);
			break;

			case 'week':
				if($week == 53 && $month == 1)
				{
					$year--;
				}

				$startd    = Qss_Lib_Date::getDateByWeek($week,$year);
				$startdate = $startd->format('Y-m-d');
				$enddate   = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');
			break;

			case 'month':

				$a           = $sonar->adjustDate($month, $year);
				$monthTemp   = $a[0];
				$yearTemp    = $a[1];
				$daysInMonth = $sonar->getDaysInMonth($monthTemp, $yearTemp);

				$startdate = sprintf('%3$s-%2$s-%1$s', '01' ,str_pad($month, 2, '0', STR_PAD_LEFT),$year);
				$enddate   = sprintf('%3$s-%2$s-%1$s', $daysInMonth ,str_pad($month, 2, '0', STR_PAD_LEFT),$year);
			break;

			case 'year':
				$daysInMonth = $sonar->getDaysInMonth(12, $year);
				$startdate   = sprintf('%3$s-%2$s-%1$s','01','01',$year);
				$enddate     = sprintf('%3$s-%2$s-%1$s',$daysInMonth,12,$year);
			break;
		}

		$eStart              = $start?$start:$startdate;
		$eEnd                = $end?$end:$enddate;

		//echo '<pre>'; print_r($eStart); die;

		$this->html->start   = Qss_Lib_Date::mysqltodisplay($eStart);
		$this->html->end     = Qss_Lib_Date::mysqltodisplay($eEnd);
		$this->html->failure = $this->getFailureMonitors($eStart, $eEnd);
	}

	public function createorderAction()
	{
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Maintenance->WorkOrder->CreateOrdersFromFailure($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

    public function assignAction()
    {
        $ifids                 = $this->params->requests->getParam('workorder', array());
        $this->html->ifids     = $ifids;
    }

    public function assigntoAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Static->M729->SaveAssign($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function createfromplansAction()
    {
        $params = $this->params->requests->getParams();
        $type   = $this->params->requests->getParam('type', '');
        $month  = (int)$this->params->requests->getParam('month', 0);
        $year   = (int)$this->params->requests->getParam('year', 0);
        $week   = (int)$this->params->requests->getParam('week', 0);
        $mSolar = new Qss_Model_Calendar_Solar();

        if($type == 'month')
        {
            $month = ($month < 10)?'0'.$month:$month;
            $start = $year.'-'.$month.'-01';
            $end   = date($year.'-'.$month.'-t');
        }
        elseif($type == 'week')
        {
            $weeks = $mSolar->getDateRangeByWeek($week, $year);
            $start = Qss_Lib_Date::displaytomysql($weeks[0]);
            $end   = Qss_Lib_Date::displaytomysql($weeks[1]);
        }
        else
        {
            return;
        }

        if ($this->params->requests->isAjax())
        {
            /*$form = new Qss_Model_Form();
            $form->init('M759', $this->_user->user_dept_id, $this->_user->user_id );

            $bash = new Qss_Bin_Process_PMNotify2($form);
            $bash->init();
            $bash->__doExecute($start, $end);*/
        	$service = $this->services->Maintenance->WorkOrder->CreateOrdersFromPlan($start, $end);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function employeesAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $tag      = $this->params->requests->getParam('tag', '');
        $request  = $mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag, array('MaNhanVien'));
        $retval   = array();

        foreach($request as $item)
        {
            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function createfromgeneralplansAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->CreateOrdersFromPlanDetails($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }




    public function generalplansAction()
    {
        $this->headScript($this->params->requests->getBasePath() . '/js/extra/static/m729.js');
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
        $ifid     = (int)$this->params->requests->getParam('general', 0);

        $this->html->generals = $this->getGeneralPlans();
        $this->html->ifid     = $ifid;
    }


    public function generalplans1Action()
    {
        $ifid     = (int)$this->params->requests->getParam('general', 0);
        $mGeneral = new Qss_Model_Maintenance_GeneralPlans();
        $main     = $mGeneral->getGeneralPlanByIFID($ifid);
        $detail   = $mGeneral->getDetailPlanByGeneralIFID($ifid);

        $this->html->generals = $this->getGeneralPlans();
        $this->html->main     = $main;
        $this->html->detail   = $detail;
        $this->html->ifid     = $ifid;
    }


    public function plansAction()
    {
        $this->headScript($this->params->requests->getBasePath() . '/js/extra/static/m729.js');
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';

        $location     = (int)$this->params->requests->getParam('location', 0);
        $mPlan        = new Qss_Model_Maintenance_Plan();

        $this->html->location      = $location;
        $this->html->locations     = $this->getLocations();
    }

    public function plans1Action()
    {
        $location     = (int)$this->params->requests->getParam('location', 0);
        $equip        = (int)$this->params->requests->getParam('equip', 0);
        $maintainType = (int)$this->params->requests->getParam('maintainType', 0);
        $mPlan        = new Qss_Model_Maintenance_Plan();

        $this->html->plans = ($location)?$mPlan->getActivePlans($location, 0, 0, $maintainType, $equip):array();
    }

    public function equipAction()
    {
        $location     = (int)$this->params->requests->getParam('location', 0);
        $this->html->location      = $location;
    }

    public function createfromplans2Action()
    {
        $params = $this->params->requests->getParams();


        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Static->M759->CreateFromPlan($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getLocations()
    {
        $mLocation = new Qss_Model_Maintenance_Location();
        $tag       = $this->params->requests->getParam('tag', '');
        $locations = $mLocation->getLocationByCurrentUser();
        $retval    = array();

        foreach($locations as $item)
        {
            $retval[$item->IOID] = str_repeat('&nbsp;', (($item->level -1)*3)) . "{$item->MaKhuVuc} - {$item->Ten}";
        }

        return $retval;
    }


    public function equipmentsAction()
    {
        $mLocation = new Qss_Model_Maintenance_Equip_List();
        $tag       = $this->params->requests->getParam('tag', '');
        $location  = $this->params->requests->getParam('location', 0);
        $locations = $mLocation->getEquipByLocation($location, $tag);
        $retval    = array();

        foreach ($locations as $item)
        {
            if($item->MaThietBi)
            {
                $display  = "{$item->MaThietBi} - {$item->TenThietBi}";
                $retval[] = array('id'=>$item->IOID, 'value'=>$display);
            }
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }

	private function getFailureMonitors($start, $end)
	{
		$common   = new Qss_Model_Extra_Extra();
		$mMonitor = new Qss_Model_Maintenance_Equip_Monitor();
		$loc      = $common->getNestedSetTable( 'OKhuVuc');
		$retval   = new stdClass();
		$j        = 0;
		$nStart   = date_create($start);
		$nEnd     = date_create($end);

		foreach($loc as $item)
		{
			$retval->{$item->IOID}->MaKhuVuc  = $item->MaKhuVuc;
			$retval->{$item->IOID}->TenKhuVuc = $item->Ten;
			$retval->{$item->IOID}->Level     = $item->LEVEL;
		}

		while($nStart <= $nEnd)
		{
			$date  = $nStart->format('Y-m-d');
			$equip = $mMonitor->getMonitorsByDate($date);

			foreach($equip as $item)
			{
				$item->LOCIOID     = (int)$item->LOCIOID;
				$item->GioiHanDuoi = (double)$item->GioiHanDuoi;
				$item->GioiHanTren = (double)$item->GioiHanTren;

				// Khong thuoc ve khu vuc nao
				if(!isset($retval->{$item->LOCIOID}))
				{
					$retval->{$item->LOCIOID}->MaKhuVuc  = $item->MaKhuVuc;
					$retval->{$item->LOCIOID}->TenKhuVuc = $item->TenKhuVuc;
					$retval->{$item->LOCIOID}->Level     = 1;
				}

				if(
					$item->NhatTrinhIOID
					&&
					( $item->DinhLuong
						&& !($item->GioiHanTren == 0 && $item->GioiHanDuoi == 0)
						&& ($item->SoGio >= $item->GioiHanTren || $item->SoGio <= $item->GioiHanDuoi)
					)
					||
					(
						!$item->DinhLuong && $item->Dat == 2
					)
				)
				{
					$retval->{$item->LOCIOID}->Equip->{$j}       = $item;
					$retval->{$item->LOCIOID}->Equip->{$j}->Ngay = $date;
					$j++;
				}
			}
			$nStart = Qss_Lib_Date::add_date($nStart, 1);
		}

		foreach($retval as $key=>$item)
		{
			if(!isset($item->Equip) || !count((array)$item->Equip))
			{
				unset($retval->{$key});
			}
		}
		//echo '<Pre>'; print_r($retval); die;

		return $retval;
	}



    public function getGeneralPlans()
    {
        $common  = new Qss_Model_Maintenance_GeneralPlans();
        $emps    = $common->getPlansByStatus(3);
        $ret     = array();

        foreach($emps as $item) {
            $ret[$item->IFID_M838] = "{$item->Ma} - {$item->Ten} (".Qss_Lib_Date::mysqltodisplay($item->NgayBatDau)
                ." - ".Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc) . ')';
        }
        return $ret;
    }

}
?>