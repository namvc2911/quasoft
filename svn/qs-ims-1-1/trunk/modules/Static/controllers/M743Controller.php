<?php

/**
 * @author: ThinhTuan
 */
class Static_M743Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
	}
	public function indexAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}

	public function showAction()
	{
		$locIOID        = $this->params->requests->getParam('location', 0);
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');
		$mBreak         = new Qss_Model_Maintenance_Breakdown();

		$this->html->start = $start;
		$this->html->end = $end;
		$this->html->eqIOID = $eqIOID;
		$this->html->eqTypeIOID = $eqTypeIOID;
		
		$this->html->cause = $mBreak->getBreakdownByReason(
							Qss_Lib_Date::displaytomysql($start)
							, Qss_Lib_Date::displaytomysql($end)
							, $eqIOID
							, $eqTypeIOID
							, $eqGroupIOID
							, $locIOID);
		
		//Lấy dữ liệu sự cố theo dây chuyền (tg dừng máy và sửa chữa)
		if(!$eqIOID)
		{
			$this->html->line = $mBreak->getBreakdownByLine(
							Qss_Lib_Date::displaytomysql($start)
							, Qss_Lib_Date::displaytomysql($end)
							, $eqIOID
							, $eqTypeIOID
							, $eqGroupIOID
							, $locIOID);
		}
		//Sự cố theo loại thiết bị
		if(!$eqTypeIOID)
		{
			$this->html->type = $mBreak->getBreakdownByEquipmentType(
							Qss_Lib_Date::displaytomysql($start)
							, Qss_Lib_Date::displaytomysql($end)
							, $eqIOID
							, $eqTypeIOID
							, $eqGroupIOID
							, $locIOID);
		}
		//lấy theo thiết bị
		if(!$eqIOID && $eqTypeIOID)
		{
			$this->html->equipment = $mBreak->getBreakdownByEquipment(
							Qss_Lib_Date::displaytomysql($start)
							, Qss_Lib_Date::displaytomysql($end)
							, $eqIOID
							, $eqTypeIOID
							, $eqGroupIOID
							, $locIOID);
		}
		//theo nguyên nhân sự cố
	}
}

?>