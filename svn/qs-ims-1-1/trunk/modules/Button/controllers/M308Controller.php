<?php
/**
 * Class Button_M408Controller
 * Các button module Nhận hàng - M408
 */
class Button_M308Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();         
    }

	public function calendarAction()
    {
    	$this->headLink($this->params->requests->getBasePath() . '/css/extra/button/m759.css');
    	$this->headScript($this->params->requests->getBasePath() . '/js/extra/button/m308.js');
    	$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$worktype = $this->params->requests->getParam('worktype',0);
		//$this->html->content = $this->views->Button->M759->Employee->Plan->Workorder($this->_user,$month,$year,$workcenter);
		$this->html->month = $month;
		$this->html->year = $year;
		$model = new Qss_Model_M319_Main();
    }
	public function calendarReloadAction()
    {
    	$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$department = $this->params->requests->getParam('department',0);
		$this->html->content = $this->views->Button->M308->Calendar($this->_user,$month,$year,$department);
		$this->html->month = $month;
		$this->html->year = $year;
    }
	public function calendarDateAction()
    {
    	//lấy lịch theo ngày
    	$model = new Qss_Model_M308_Main();
    	$eid = $this->params->requests->getParam('eid',0);
		$date = $this->params->requests->getParam('date');
		$this->html->eid = $eid;
		$this->html->date = $date;
		$table = Qss_Model_Db::Table('ODanhSachNhanVien');
		$table->where(sprintf('IOID=%1$d',$eid));
		$this->html->employee = $table->fetchOne(); 
		$shift = Qss_Model_Db::Table('OCa');
		$this->html->shift = $shift->fetchAll();
		$calendar = $model->getEmpCalendarByDate($eid,$date);
		if(!$calendar)//khởi tạo cái trống
		{
			$tungay = Qss_Lib_Date::i_fMysql2Time($date);//00h nó nghĩ là ngày hôm trước
			$tungay = strtotime('monday this week',$tungay);
			$denngay = strtotime('next sunday',$tungay);
			$calendar = new stdClass();
			$calendar->IFID_M308 = 0;
			$calendar->DeptID = $this->_user->user_id;
			$calendar->TuNgay = date('Y-m-d',$tungay);
			$calendar->DenNgay = date('Y-m-d',$denngay);
			$calendar->Ref_ThuHai = 0;
			$calendar->Ref_ThuBa = 0;
			$calendar->Ref_ThuTu = 0;
			$calendar->Ref_ThuNam = 0;
			$calendar->Ref_ThuSau = 0;
			$calendar->Ref_ThuBay = 0;
			$calendar->Ref_ChuNhat = 0;
			$calendar->GhiChu = '';
		}
		$this->html->calendar = $calendar;
    }
	public function calendarSaveAction()
    {
    	$params = $this->params->requests->getParams();
		$service = $this->services->Form->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
    }
	public function calendarCancelAction()
    {
		$ifid = $this->params->requests->getParam('ifid', array());
		$deptid = $this->params->requests->getParam('deptid', array());
		$service = $this->services->Form->Delete($ifid, $deptid);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
    }

}