<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M793Controller extends Qss_Lib_Controller
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
	 * In ke hoach bao tri theo khoang thoi gian
	 */
	public function indexAction()
	{

	}

	/**
	 *
	 */
	public function showAction()
	{
		$sDate       = $this->params->requests->getParam('sdate', 0);
		$eDate       = $this->params->requests->getParam('edate', 0);
		$locIOID     = $this->params->requests->getParam('loc_ioid', 0);
		$eqGroupIOID = $this->params->requests->getParam('eq_group_ioid', 0);
		$eqTypeIOID  = $this->params->requests->getParam('eq_type_ioid', 0);
		$mTypeIOID   = $this->params->requests->getParam('maint_type_ioid', 0);
		$mSDate      = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate      = Qss_Lib_Date::displaytomysql($eDate);


		$this->html->report = $this->getPlansForPlanDetailReport(
		$mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID, $mTypeIOID
		);

		$this->html->start = $sDate;
		$this->html->end   = $eDate;
	}

	private function getPlansForPlanDetailReport($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $mTypeIOID= 0)
	{
		$planModel   = new Qss_Model_Maintenance_Plan();
		$planDetail  = array();
		$planIFIDArr = array();
		$cEDate      = date_create($mEDate);
        $cSDate      = date_create($mSDate);

		while($cSDate <= $cEDate) {
            $objPlans = $planModel->getAllPlansByDate(
                $cSDate->format('Y-m-d')
                , $locIOID
                , 0
                , $eqGroupIOID
                , $eqTypeIOID
            );

            foreach ($objPlans as $plan) {
                $planDetail[$plan->IFID_M724]['General'] = $plan;
                $planDetail[$plan->IFID_M724]['Date'][]  = $cSDate->format('Y-m-d');

                if(!in_array($plan->IFID_M724, $planIFIDArr)) {
                    $planIFIDArr[] = $plan->IFID_M724;
                }
            }

            $cSDate = Qss_Lib_Date::add_date($cSDate, 1);
        }

		$materials = $planModel->getMaterialsByPlanIFID($planIFIDArr);
        $tasks     = $planModel->getTasksByPlanIFID($planIFIDArr);

		foreach($materials as $m) {
			$planDetail[$m->IFID]['Materials'][] = $m;
		}

		foreach($tasks as $m) {
			$planDetail[$m->IFID]['Tasks'][] = $m;
		}

		return $planDetail;
	}
}

?>