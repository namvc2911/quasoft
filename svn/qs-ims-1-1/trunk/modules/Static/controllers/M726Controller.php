<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M726Controller extends Qss_Lib_Controller
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
	}
	

	public function indexAction()
	{

	}

	public function showAction()
	{
		// DIEU KIEN LOC THEO THIET BI
		$eqIOID         = $this->params->requests->getParam('equip', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
		// DIEU KIEN LOC THEO TRUNG TAM CHI PHI
		$costcenterIOID = $this->params->requests->getParam('costcenter', 0);
		$costcenter     = $this->params->requests->getParam('costcenterStr', 0);

		$sort           = $this->params->requests->getParam('sort', 0);
		$model          = new Qss_Model_Maintenance_Equipment();

		$eqArr = array();
		if($eqIOID)
		{
			$eqArr = array($eqIOID);
		}


		$eqs = $model->getEquipments(
					$eqArr,
					$locationIOID,
					$costcenterIOID,
					$eqGroupIOID,
					$eqTypeIOID,
					$sort
					);

		// Lấy ioid danh sách thiết bị
		$resultEqIOIDArr = array(0);
		foreach($eqs as $e)
		{
			$resultEqIOIDArr[] = $e->HPEQIOID;

			if($e->CEQIOID)
			$resultEqIOIDArr[] = $e->CEQIOID;
		}

		$lichdieudong    = $model->getMoveCalByDateOfEquips(date('Y-m-d'), $resultEqIOIDArr);
		$lichdieudongArr = array();

		foreach($lichdieudong as $item)
		{
			$lichdieudongArr[$item->Ref_MaThietBi]['Project'] = $item->DuAn;
		}

		//echo '<pre>'; print_r(); die;

		$this->html->deptid          = $this->_user->user_dept_id;
		$this->html->lichdieudong    = $lichdieudongArr;
		$this->html->eqs             = $eqs;
		$this->html->tech            = $this->params->requests->getParam('tech', 0);
		$this->html->sort            = $sort;
		// LOC THEO KHU VUC
		$this->html->locationIOID    = $locationIOID;
		$this->html->location        = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID     = $eqGroupIOID;
		$this->html->eqGroup         = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID      = $eqTypeIOID;
		$this->html->eqType          = $eqType;
		// LOC THEO THIET BI
		$this->html->eqIOID          = $eqIOID;
		$this->html->eq              = $eq;
		// LOC THEO TTCP
		$this->html->costcenterIOID  = $costcenterIOID;
		$this->html->costcenter      = $costcenter;
	}

}

?>