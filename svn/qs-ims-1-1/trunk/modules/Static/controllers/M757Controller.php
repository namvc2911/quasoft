<?php
class Static_M757Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
	}

	public function indexAction()
	{

	}

	public function showAction()
	{
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', '');
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', '');
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', '');

		$this->html->report       = $this->getReportData($locationIOID, $eqGroupIOID, $eqTypeIOID);
		$this->html->locationIOID = $locationIOID;
		$this->html->location     = $location;
		$this->html->eqGroupIOID  = $eqGroupIOID;
		$this->html->eqGroup      = $eqGroup;
		$this->html->eqTypeIOID   = $eqTypeIOID;
		$this->html->eqType       = $eqType;
	}

    public function getReportData($locationIOID, $eqGroupIOID, $eqTypeIOID)
    {
        $mList  = new Qss_Model_Maintenance_Equip_List();
        $equips = $mList->getEquipments($locationIOID, $eqGroupIOID, $eqTypeIOID);

        foreach($equips as $item)
        {
            $compare       = $item->HanBaoHanh?Qss_Lib_Date::compareTwoDate(date('Y-m-d'), $item->HanBaoHanh):'';
            $interval      = $item->HanBaoHanh?Qss_Lib_Date::diffTime(date('Y-m-d'), $item->HanBaoHanh, 'D'):'';
            $item->EStatus = $item->HanBaoHanh?(($compare==0)?1:(($compare==-1)?(($interval <= 7)?2:3):4)):0;
            $item->EClass  = $item->HanBaoHanh?(($compare==0)?'now_16':(($compare==-1)?(($interval <= 7)?'duesoon_16':'normal_16'):'overdue_16')):'question_16';
        }
        return $equips;
    }
}

?>