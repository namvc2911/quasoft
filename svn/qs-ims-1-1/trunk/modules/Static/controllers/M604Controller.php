<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M604Controller extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Inventory_Cost();
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/extra/maintenance/calendar.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
	}
	public function indexAction ()
	{
		//liệt kê các kỳ đã tính giá thành = cách kiểm tra tồn tại bảng dữ liệu tblcostmmyyyy
		//$this->_model->createTable(1, 2014);
		$this->html->all = $this->_model->getAll();

	}
	public function exceldAction ()
	{
		//export to excel
	}
	public function importAction ()
	{
		//import giá từ excel
	}
	public function calculateAction ()
	{
		//Tính toán giá thành
		$month = $this->params->requests->getParam('month',0);
		$year = $this->params->requests->getParam('year',0);
		//chạy lại từ tháng tính tới tháng hiện tại
		$cmonth = date('m');
		$cyear = date('Y');
		if($cyear > $year)
		{
			$cmonth = 12;
		}
		while($month <= $cmonth)
		{
			$this->_model->calculate($month, $year);
			$month++;
		}
		echo Qss_Json::encode(array('error'=>false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function removeAction ()
	{
		//Tính toán giá thành
		$month = $this->params->requests->getParam('month',0);
		$year = $this->params->requests->getParam('year',0);
		$this->_model->dropTable($month, $year);
		echo Qss_Json::encode(array('error'=>false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>