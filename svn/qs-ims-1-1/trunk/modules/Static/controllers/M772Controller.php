<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M772Controller extends Qss_Lib_Controller
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
	 * Báo cáo so sánh định mức
	 */
	public function indexAction()
	{
	  
	}

	public function showAction()
	{
		$equipModel  = new Qss_Model_Maintenance_Equipment();
		$startDate   = $this->params->requests->getParam('start_date' , '');
		$endDate     = $this->params->requests->getParam('end_date' , '');
		$equipIOID   = $this->params->requests->getParam('equip_ioid' , 0);
		$itemIOID    = $this->params->requests->getParam('item_ioid' , 0);
		$locIOID     = $this->params->requests->getParam('location_ioid' , 0);
		$eqGroupIOID = $this->params->requests->getParam('eq_group_ioid' , 0);
		$eqTypeIOID  = $this->params->requests->getParam('eq_type_ioid' , 0);
	  
		if(!$startDate || !$endDate) // Invalid
		{
			$this->html->report = array();
		}
		else
		{
			$this->html->startDate = $startDate;
			$this->html->endDate   = $endDate;
			$this->html->report    = $this->_model->getMaterialsCompare(
						Qss_Lib_Date::displaytomysql($startDate),
						Qss_Lib_Date::displaytomysql($endDate),
						$locIOID,
						$eqGroupIOID,
						$eqTypeIOID,
						$equipIOID,
						$itemIOID
						);
		}
	}
}

?>