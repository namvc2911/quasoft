<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M749Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */
	protected $_params; /* Remove */
	protected $_common; /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_params    = $this->params->requests->getParams();
		$this->_common    = new Qss_Model_Extra_Extra();
		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}


	public function indexAction()
	{

	}

	public function showAction()
	{
		$eqIOID = $this->params->requests->getParam('equip_ioid', 0);
		$sDate  = $this->params->requests->getParam('start', 0);
		$eDate  = $this->params->requests->getParam('end', 0);
		$mSDate = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate = Qss_Lib_Date::displaytomysql($eDate);
		$this->html->report = $this->_model->getPerformance($mSDate, $mEDate, $eqIOID);
		$this->html->startDate = $sDate;
		$this->html->endDate = $eDate;
	}
	}

?>