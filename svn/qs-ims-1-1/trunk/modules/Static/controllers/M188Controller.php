<?php
class Static_M188Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        //echo Qss_Lib_Date::diffTime('2017-10-01', '2018-12-25', 'MO'); die;
        $this->_model = new Qss_Model_Maintenance_Calendar();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/maintenance/pm-calendar.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts-more.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/hightchart/themes/grid.js');
        $this->headLink($this->params->requests->getBasePath()   . '/css/calendar.css');
    }

    /**
     * Chuyển kế hoạch M724 thành phiếu bảo trì M759
     */
    public function convertAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Static->M188->CreateWorkorders($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    public function indexAction ()
    {
        $type = $this->params->requests->getParam('type', '');

        if(!$type)
        {
            $type = $this->params->cookies->get('cal_maintenance_type', 'week');
        }

        $day        = $this->params->requests->getParam('day',date('d'));
        $week       = $this->params->requests->getParam('week',date('W'));
        $month      = $this->params->requests->getParam('month',date('m'));
        $year       = $this->params->requests->getParam('year',date('Y'));
        $workcenter = $this->params->requests->getParam('workcenter',0);
        $responseid = $this->params->requests->getParam('responseid',0);
        $assignid   = $this->params->requests->getParam('assignid',0);

        switch($type)
        {
            case 'week':
                $this->html->content = $this->views->Maintenance->Plan->Calendar->Content->Week(
                    $this->_user,$week,$month,$year,$workcenter,$responseid,$assignid);
                break;

            case 'month':
                $this->html->content = $this->views->Maintenance->Plan->Calendar->Content->Month(
                    $this->_user,$month,$year,$workcenter,$responseid,$assignid);
                break;
        }


        $this->html->day          = $day;
        $this->html->week         = $week;
        $this->html->month        = $month;
        $this->html->year         = $year;
        $this->html->type         = $type;
        $this->html->workcenter   = $workcenter;
        $this->html->responseid   = $responseid;
        $this->html->assignid     = $assignid;
        $model                    = new Qss_Model_Extra_Extra();
        $this->html->workcenters  = $model->getTable(array('*'),'ODonViSanXuat');
        $this->html->employees    = $model->getTable(array('*'),'ODanhSachNhanVien');


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
			case 'week':
				$this->html->content = $this->views->Maintenance->Plan->Calendar->Content->Week($this->_user,$week,$month,$year,$workcenter,$responseid);
				break;
			case 'month':
				$this->html->content = $this->views->Maintenance->Plan->Calendar->Content->Month($this->_user,$month,$year,$workcenter,$responseid);
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
	public function materialAction()
	{
		$type  = $this->params->requests->getParam('type','week');
		$day   = $this->params->requests->getParam('day',date('d'));
		$week  = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year  = $this->params->requests->getParam('year',date('Y'));

		$mOrder    = new Qss_Model_Maintenance_Plan();
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

		$this->html->material = $mOrder->getPlanMaterialGroupByEquip($startdate, $enddate);
	}
	public function adjustAction()
	{
		$ifid = $this->params->requests->getParam('ifid');
		$chukyid = $this->params->requests->getParam('chukyid');
		$date = $this->params->requests->getParam('date');
		$dvbtid = (int)$this->params->requests->getParam('dvbtid');
		$model = new Qss_Model_Extra_Extra();
		$chuky = $model->getTableFetchOne('OChuKyBaoTri','IOID='.$chukyid);
		$arrUpdate = array('OBaoTriDinhKy'=>array(0=>array('DVBT'=>$dvbtid)));
		if($chuky && !$chuky->CanCu)
		{
			$ndate = date_create($date);
			
			$arrUpdate['OChuKyBaoTri'][0]['ioid']= (int) $chukyid;
			switch ($chuky->KyBaoDuong)
			{
				case 'W':
					$arrUpdate['OChuKyBaoTri'][0]['Thu']= date_format($ndate,'w');
					break;
				case 'M':
					$arrUpdate['OChuKyBaoTri'][0]['Ngay']= date_format($ndate,'d');
					break;
				case 'Y':
					$arrUpdate['OChuKyBaoTri'][0]['Ngay']= date_format($ndate,'d');
					$arrUpdate['OChuKyBaoTri'][0]['Thang']= date_format($ndate,'m');
					break;
			}
		}
		$service = $this->services->Form->Manual('M724',$ifid,$arrUpdate);
		//echo Qss_Json::encode(array('error'=>false));
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
}