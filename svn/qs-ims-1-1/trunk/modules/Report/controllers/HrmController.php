<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Report_HrmController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public $limit;
	private $_common;

	public function init()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->limit = Qss_Lib_Extra_Const::$DATE_LIMIT;
		$this->_common = new Qss_Model_Extra_Extra();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->html->curl = $this->params->requests->getRequestUri();
	}
	/*
	 * Báo cáo chấm công theo ngày
	 */

	public function timesheetDailyAction()
	{
		//$this->html->departments = $this->getDepartment();
		$this->html->limit = $this->limit;
		$this->html->employees = $this->_common->getTable(array('*')
		, 'ODanhSachNhanVien',
		array()
		, array('MaNhanVien'), 'NO_LIMIT');
	}

	public function timesheetDaily1Action()
	{
		$hrm = new Qss_Model_Extra_Hrm();
		$params = $this->params->requests->getParams();
		$start = Qss_Lib_Date::displaytomysql($params['start']);
		$end = Qss_Lib_Date::displaytomysql($params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D', $this->limit); // End date by limit time
		$timesheet = $hrm->getDailyTimesheet($start, $end,
		$params['department'], $params['employee']);

		$this->html->timesheet = $timesheet;
		$this->html->startDate = $params['start'];
		$this->html->endDate = date('d-m-Y', strtotime($end));
	}
	/*
	 * Báo cáo chấm công theo tháng
	 */

	public function timesheetMonthlyAction()
	{
		//$this->html->departments = $this->getDepartment();
	}

	public function timesheetMonthly1Action()
	{
		$hrm = new Qss_Model_Extra_Hrm();
		$extra = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$leave = $hrm->countByLeaveTypes($params['month'],
		$params['year'], $params['department']);
		$countLeaveByTypes = array(); // Đếm số ngày nhỉ theo phân loại nghỉ

		foreach ($leave as $lea)
		{
			if (isset($countLeaveByTypes[$lea->Ref_MaNV][$lea->Ref_MaNghi]))
			{
				$countLeaveByTypes[$lea->Ref_MaNV][$lea->Ref_MaNghi] += $lea->DemNghi ? $lea->DemNghi : 0;
			}
			else
			{
				$countLeaveByTypes[$lea->Ref_MaNV][$lea->Ref_MaNghi] = $lea->DemNghi ? $lea->DemNghi : 0;
			}
		}

		$this->html->leaveTypes = $extra->getTable(array('*'),
                        'OPhanLoaiNghi', array(), array(), 'NO_LIMIT');
		$this->html->month = $params['month'];
		$this->html->year = $params['year'];
		$this->html->timesheet = $hrm->getMontlyTimesheet($params['month'],
		$params['year'], $params['department']);
		$this->html->countLeaveByTypes = $countLeaveByTypes;
	}
	/*
	 * Biểu đồ chấm công
	 */

	public function timesheetChartAction()
	{
		//$this->html->departments = $this->getDepartment();
	}

	public function timesheetChart1Action()
	{
		$hrm = new Qss_Model_Extra_Hrm();
		$params = $this->params->requests->getParams();
		$limit = $limit = array('D' => 32, 'W' => 24, 'M' => 24, 'Q' => 24);
		$start = Qss_Lib_Date::displaytomysql($params['start']); /* Ngày bắt đầu dạng Y-m-d */
		$end = Qss_Lib_Date::displaytomysql($params['end']); /* Ngày kết thúc dạng Y-m-d */
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D', $limit); /* Lấy ngày kết thúc theo $limit */
		$timesheet = $hrm->getTimesheetChartData($start, $end,
		$params['period'], $params['department'],
		$params['employee']);

		$this->html->timesheet = $timesheet;
		$this->html->period = $params['period'];
		$this->html->startDate = $params['start'];
		$this->html->endDate = date('d-m-Y', strtotime($end));
	}
}
