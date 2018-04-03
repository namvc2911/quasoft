<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M739Controller extends Qss_Lib_Controller
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
		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	public function indexAction()
	{
		$this->html->thietbi = $this->_model->getEquipmentHasBOM();
	}

	public function showAction()
	{
		$eqIOID   = $this->params->requests->getParam('line', 0);
		$itemIOID = $this->params->requests->getParam('item', 0);
		$sDate    = $this->params->requests->getParam('start', 0);
		$eDate    = $this->params->requests->getParam('end', 0);
		$mSDate   = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate   = Qss_Lib_Date::displaytomysql($eDate);
		$this->html->report = $this->_model->getPerformance($mSDate, $mEDate, $eqIOID, $itemIOID);

	}
	public function show1Action()
	{
		$eqIOID     = $this->params->requests->getParam('line', 0);
		$sDate      = $this->params->requests->getParam('start', 0);
		$eDate      = $this->params->requests->getParam('end', 0);

		$this->html->items = $this->_model->getItemsFromBOM(
		$eqIOID,
		Qss_Lib_Date::displaytomysql($sDate),
		Qss_Lib_Date::displaytomysql($eDate)
		);
	}
	}

?>