<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M792Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();
	}
    /**
	 * Tong hop ke hoach bao tri theo nam
	 */
	public function indexAction()
	{

	}

	public function showAction()
	{

	    $model  = new Qss_Model_M724_Plan();
		$year         = $this->params->requests->getParam('year', 0);
		if($year)
		{
			$equipIOID    = $this->params->requests->getParam('equipment', 0);
			$locationIOID = $this->params->requests->getParam('location', 0);
			$eqGroupIOID  = $this->params->requests->getParam('group', 0);
			$eqTypeIOID   = $this->params->requests->getParam('type', 0);
            $workcenter   = $this->params->requests->getParam('workcenter', '');


			$this->html->report           = $model->getYearReportWithMaterials($year, $locationIOID, $eqGroupIOID, $eqTypeIOID, $equipIOID, $workcenter);
			$this->html->equipIOID        = $equipIOID;
			$this->html->equip            = $this->params->requests->getParam('equipmentStr', '');
			$this->html->locationIOID     = $locationIOID;
			$this->html->location         = $this->params->requests->getParam('locationStr', '');
			$this->html->eqGroupIOID      = $eqGroupIOID;
			$this->html->eqGroup          = $this->params->requests->getParam('groupStr', '');
			$this->html->eqTypeIOID       = $eqTypeIOID;
			$this->html->eqType           = $this->params->requests->getParam('typeStr', '');
			$this->html->year             = $year;
		}

	}
}

?>