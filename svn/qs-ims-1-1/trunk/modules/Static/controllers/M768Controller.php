<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M768Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Workorder();
	}
	public function indexAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}
	
	public function showAction()
	{
		// Hien thi vat tu tieu hao gop theo vat tu
		// Get Filter
		$sDate         = $this->params->requests->getParam('start');
		$eDate         = $this->params->requests->getParam('end');
		$eqGroupIOID   = $this->params->requests->getParam('group');
		$eqTypeIOID    = $this->params->requests->getParam('type');
		$locIOID       = $this->params->requests->getParam('location');

		// Get time
		$mSDate        = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate        = Qss_Lib_Date::displaytomysql($eDate);
		//$mEDate        = Qss_Lib_Extra::getEndDate($mSDate, $mEDate);
		//$eDate         = Qss_Lib_Date::mysqltodisplay($mEDate);

        $maintainModel = new Qss_Model_Maintenance_Plan();
        $materialPlans = $maintainModel->getPlanMaterial($mSDate, $mEDate, $locIOID , $eqGroupIOID , $eqTypeIOID);

		$this->html->print = $materialPlans;
		$this->html->start = $sDate;
		$this->html->end   = $eDate;
	}
}

?>