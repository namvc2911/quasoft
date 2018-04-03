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
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

        $this->_model     = new Qss_Model_Maintenance_Equip_Operation();

        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
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
        $mLocation        = new Qss_Model_Maintenance_Location();
        $mEquip           = new Qss_Model_Maintenance_Equip_List();
        $sDate       = $this->params->requests->getParam('sdate', 0);
        $eDate       = $this->params->requests->getParam('edate', 0);
        $locIOID     = $this->params->requests->getParam('loc_ioid', 0);
        $eqGroupIOID = $this->params->requests->getParam('eq_group_ioid', 0);
        $eqTypeIOID  = $this->params->requests->getParam('eq_type_ioid', 0);
        $mTypeIOID   = $this->params->requests->getParam('maint_type_ioid', 0);
        $eqIOID      = $this->params->requests->getParam('eq_ioid', 0);
        $mSDate      = Qss_Lib_Date::displaytomysql($sDate);
        $mEDate      = Qss_Lib_Date::displaytomysql($eDate);
        $equipDetail      = $mEquip->getEquipByIOID($eqIOID);
        $locationDetail   = $equipDetail?$mLocation->getLocationByIOID((int)$equipDetail->Ref_MaKhuVuc):NULL;

        $this->html->deptid          = $this->_user->user_dept_id;
        $this->html->report = $this->getPlansForPlanDetailReport(
            $mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID, $mTypeIOID, $eqIOID
        );

        $this->html->start = $sDate;
        $this->html->end   = $eDate;
        $this->html->equip    = $equipDetail;
        $this->html->location = $locationDetail;
    }

    private function getPlansForPlanDetailReport($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $mTypeIOID= 0, $equipIOID=0)
    {
        $planModel  = new Qss_Model_Maintenance_Plan();
        $planDetail = array();
        $cSDate     = date_create($mSDate);
        $cEDate     = date_create($mEDate);

        while($cSDate <= $cEDate)
        {
            $plans      = $planModel->getPlanConfigs($locIOID, $eqTypeIOID, $eqGroupIOID, $equipIOID, $cSDate->format('Y-m-d'));
            $planDetail = array_merge($planDetail, $plans);
            $cSDate     = Qss_Lib_Date::add_date($cSDate, 1);
        }
        //echo '<pre>'; print_r($planDetail); die;
        return $planDetail;
    }
}

?>