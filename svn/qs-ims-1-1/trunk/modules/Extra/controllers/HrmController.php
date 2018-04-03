<?php
class Extra_HrmController extends Qss_Lib_Controller
{
	private $_defaultCurrency = 'VND'; /* Loai tien mac dinh */

	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
	}

	public function salaryPayrollPrintAction()
	{
		$extra               = new Qss_Model_Extra_Extra();
		$this->html->ifid    = $this->params->requests->getParam('ifid',0);
		//$this->html->deptid  = $this->_user->user_dept_id;
	}

	public function salaryPayrollPrint1Action()
	{
		$model                  = new Qss_Model_Extra_Hrm();
		$extra                  = new Qss_Model_Extra_Extra();
		$solar                  = new Qss_Model_Calendar_Solar();
		$ifid                   = $this->params->requests->getParam('ifid',0);
		$department             = $this->params->requests->getParam('department',0);
		$employee               = $this->params->requests->getParam('employee',0);
		$this->html->payroll    = $model->getPayroll($ifid, $department, $employee);
		$this->html->info       = $extra->getTable(array('Nam, Thang'),'OBangLuong', array('IFID_M327'=>$ifid) , array(),'NO_LIMIT', 1);
		$this->html->type       = $employee?1:2;// 1- In một nhân viên, 2 - In một hoặc nhiều phòng
		$this->html->currency   = $extra->getDefaultCurrency($this->_defaultCurrency);/* Lay loai tien */
		$this->html->daysInMonth = $solar->getDaysInMonth(date('m'), date('Y')); /* So ngay trong thang */
		$this->html->overtime   = $extra->getTable(array('*'), 'OKieuLamThem', array(), array('PhanTram asc'), 'NO_LIMIT');
		$this->html->allowances = $extra->getTable(array('*'), 'OLoaiPhuCap', array(), array('Ma asc'), 'NO_LIMIT' );
		$this->layout           = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . 'print.php';
	}
	public function timesheetAttendanceAction()
	{

	}
}