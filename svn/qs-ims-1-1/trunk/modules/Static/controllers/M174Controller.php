<?php

/**
 * Class Static_M174Controller
 * Viết cái gì vào câu truyện cổ tích này đây.
 * Thật oải, tâm trạng không tốt
 */
class Static_M174Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
        $this->headLink($this->params->requests->getBasePath() . '/css/print.css');
    }

    /**
     * >>> Hien thi cac tab
     */
    public function indexAction()
    {
        $mSystem      = new Qss_Model_System();
        $appproveList = $mSystem->getAllApproveByUser($this->_user->user_id);
        $count        = array();

        $formM753  = new Qss_Model_Form();
        $formM753->init('M753', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM753 = $this->getFormRights($formM753);

        $formM747  = new Qss_Model_Form();
        $formM747->init('M747', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM747 = $this->getFormRights($formM747);

        $formM759  = new Qss_Model_Form();
        $formM759->init('M759', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM759 = $this->getFormRights($formM759);

        $formM602  = new Qss_Model_Form();
        $formM602->init('M602', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM602 = $this->getFormRights($formM602);

        $this->html->rightM753  = $rightM753&1 || $rightM753&2 || $rightM753&4;
        $this->html->rightM747  = $rightM747&1 || $rightM747&2 || $rightM747&4;
        $this->html->rightM759  = $rightM759&1 || $rightM759&2 || $rightM759&4;
        $this->html->rightM602  = $rightM602&1 || $rightM602&2 || $rightM602&4;
        $this->html->rightM504 = Qss_Lib_System::getFormRights('M504', $this->_user->user_group_list);

        // echo '<pre>'; print_r($appproveList); die;

        foreach ($appproveList as $item)
        {
            $count[$item->SAID] = $mSystem->countPendingApprovalsBySAID($item->SAID);
        }

        $this->html->approveList           = $appproveList;
        $this->html->countPendingApprovals = $count;
    }

    public function approverAction()
    {
        $said       = $this->params->requests->getParam('said', '');
        $mSystem    = new Qss_Model_System();
        $mObject    = new Qss_Model_Object();
        $page       = $this->params->requests->getParam('page', 1);
        $perpage    = $this->params->requests->getParam('perpage', 20);
        $page       = $page?$page:1;
        $perpage    = $perpage?$perpage:20;
        $end        = ($page - 1) * $perpage;
        $form       = new Qss_Model_Form();


        $total      = $mSystem->countApproveListBySAID($said);
        $totalPage  = ceil($total/$perpage);
        $approval   = $mSystem->getApprovalBySAID($said);

        if($approval)
        {
            $mObject->v_fInit($approval->MainObjectCode, $approval->FormCode);
            $form->init($approval->FormCode);
        }

        $this->html->fields   = $approval?$mObject->loadFields($this->_user):array();
        $this->html->deptid   = $this->_user->user_dept_id;
        $this->html->list     = $mSystem->getApproveListBySAID($said, $page, $perpage, true);
        $this->html->total    = $totalPage;
        $this->html->perpage  = $perpage;
        $this->html->page     = $page;
        $this->html->next     = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev     = (($page - 1) < 1)?1:($page - 1);
        $this->html->SAID     = $said;
        $this->html->approval = $approval;
        $this->html->end      = $end;
        $this->html->form     = $form;
    }

    public function requestsAction()
    {
        $start              = $this->params->requests->getParam('m174_start', '');
        $end                = $this->params->requests->getParam('m174_end', '');
        $loc                = $this->params->requests->getParam('m174_location', 0);
        $equipGroup         = $this->params->requests->getParam('m174_group', 0);
        $equipID            = $this->params->requests->getParam('m174_equip', 0);
        $mStart             = Qss_Lib_Date::displaytomysql($start);
        $mEnd               = Qss_Lib_Date::displaytomysql($end);
        $mRequest           = new Qss_Model_Maintenance_Request();
        $this->html->data   = $mRequest->getRequestsDontClose($mStart, $mEnd, $loc, $equipID, $equipGroup);
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function workordersAction()
    {
        $start                 = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end                   = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $loc                   = $this->params->requests->getParam('m174_location', 0);
        $equipGroup            = $this->params->requests->getParam('m174_group', 0);
        $equipID               = $this->params->requests->getParam('m174_equip', 0);
        $mStart                = Qss_Lib_Date::displaytomysql($start);
        $mEnd                  = Qss_Lib_Date::displaytomysql($end);
        $mWorkorder            = new Qss_Model_Maintenance_Workorder();
        $this->html->dueOrders = $mWorkorder->getIssueWorkOrdersInRange($mStart, $mEnd, $loc, $equipID, $equipGroup);
        $this->html->deptid    = $this->_user->user_dept_id;
    }
 	public function preventiveAction()
    {
        $start                 = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end                   = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $loc                   = $this->params->requests->getParam('m174_location', 0);
        $equipGroup            = $this->params->requests->getParam('m174_group', 0);
        $equipID               = $this->params->requests->getParam('m174_equip', 0);
        $mStart                = Qss_Lib_Date::displaytomysql($start);
        $mEnd                  = Qss_Lib_Date::displaytomysql($end);
        $mWorkorder            = new Qss_Model_Maintenance_Workorder();
        $this->html->dueOrders = $mWorkorder->getIssueWorkOrdersInRange($mStart, $mEnd, $loc, $equipID, $equipGroup);
        $this->html->deptid    = $this->_user->user_dept_id;
        
        $start                 = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end                   = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $location     = (int)$this->params->requests->getParam('m174_location',  0);
        $equipGroup            = $this->params->requests->getParam('m174_group', 0);
        $equip        = (int)$this->params->requests->getParam('m174_group', 0);
        $nextBack     = $this->params->requests->getParam('nextBack', 1);
        $nextStart    = $this->params->requests->getParam('nextStart', $start);
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $end) == 1?$end:$nextStart;
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $start) == -1?$start:$nextStart;

        $this->html->plans     = ($start && $end)?$this->_addBasicPlan($start, $end, $nextStart, $nextBack, $location,$group, $equip):array();
        $this->html->nextStart = $this->m759_plans_next_start;
    }

	private function _addBasicPlan($startDate, $endDate, $nextStart, $nextBack, $locIOID = 0, $group = 0, $equip = 0) {
        $cNextStart = date_create($nextStart);
        $tNextStart = $cNextStart;

        $arrChiSo   = array();
        $retval     = array();
        $limit      = 100;
        $i          = -1;
        $count      = ($nextBack)?0:$limit;

        while (true)
        {
            $date      = $cNextStart->format('Y-m-d');

            if(!Qss_Lib_Date::checkInRangeTime($date, $startDate, $endDate)) // Ra khoi khoang thoi gian
            {
                break;
            }

            $basicplan = Qss_Model_Maintenance_Plan::createInstance();
            $plans 	   = $basicplan->getPlansByDate($date, $locIOID, 0, 0, $group, 0, $equip);

            foreach ($plans as $plan) {
                $plan->NgayKetThuc = ($plan->NgayKetThuc == '0000-00-00')?'':$plan->NgayKetThuc;
                $khuvuc            = (int) $plan->Ref_KhuVuc;
                $thietbi           = (int) $plan->Ref_MaThietBi;
                $bophan            = (int) $plan->Ref_BoPhan;
                $chuky             = (int) $plan->ChuKyIOID;

                if(($plan->CanCu == 1 || $plan->CanCu == 2) && isset($arrChiSo[$khuvuc][$thietbi][$bophan][$chuky])) {

                }
                else {

                    $retval[++$i]     = $plan;
                    $retval[$i]->Date = $date;
                    $count            = ($nextBack)?($count+1):($count-1);
                }

                if($plan->CanCu == 1 || $plan->CanCu == 2) {
                    $arrChiSo[$khuvuc][$thietbi][$bophan][$chuky] = 1;
                }
            }

            if($nextBack) {
                if(count($retval) && $count >= $limit) {
                    $temp = Qss_Lib_Date::add_date($cNextStart, 1);
                    $this->m759_plans_next_start = $temp->format('Y-m-d');
                    break;
                }
            }
            else
            {
                if(count($retval) && $count <= 0) {
                    $temp = Qss_Lib_Date::add_date($tNextStart, 1);
                    $this->m759_plans_next_start = $temp->format('Y-m-d');
                    break;
                }
            }

            if($nextBack) {
                $cNextStart = Qss_Lib_Date::add_date($cNextStart, 1);
            }
            else
            {
                $cNextStart = Qss_Lib_Date::diff_date($cNextStart, 1);
            }

        }

        return $retval;
    }
    /**
     * Kiểm định, hiệu chuẩn đến hạn: Các phiếu bảo trì hiệu chuẩn chưa đóng trong khoảng thời gian hoặc trước ngày hôm nay
     * @todo: Chưa lấy từ kế hoạch, chưa tính ngày kiểm định tiếp theo (có thể không cần)
     */
    public function duecalibrationsAction()
    {
        $start        = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end          = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $loc          = $this->params->requests->getParam('m174_location', 0);
        $equipGroup   = $this->params->requests->getParam('m174_group', 0);
        $equipID      = $this->params->requests->getParam('m174_equip', 0);
        $mStart       = Qss_Lib_Date::displaytomysql($start);
        $mEnd         = Qss_Lib_Date::displaytomysql($end);
        $mCalibration = new Qss_Model_Maintenance_Equip_Calibration();

        $this->html->calibrations = $mCalibration->getProcessingCalibrationsFormCalibrationAndVerify($mStart, $mEnd, $loc, $equipID, $equipGroup);

        if(Qss_Lib_System::formActive('M753'))
        {
            $this->html->calibrations = $mCalibration->getProcessingCalibrationsFormCalibrationAndVerify($mStart, $mEnd, $loc, $equipID, $equipGroup);
        }
        else
        {
            $this->html->calibrations = $mCalibration->getProcessingCalibrationsFormWorkOrder($mStart, $mEnd, $loc, $equipID, $equipGroup);
        }
        $this->html->deptid       = $this->_user->user_dept_id;
    }

    /**
     * >>> Phieu bao tri den han
     */
    public function dueworkordersAction()
    {
        $start      = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end        = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $loc        = $this->params->requests->getParam('m174_location', 0);
        $equipGroup = $this->params->requests->getParam('m174_group', 0);
        $equipID    = $this->params->requests->getParam('m174_equip', 0);
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $this->html->dueOrders    = $mWorkorder->getIssueWorkOrdersInRange($mStart, $mEnd, $loc, $equipID, $equipGroup);
        $this->html->deptid       = $this->_user->user_dept_id;
    }

    /**
     * >>> Phieu bao tri qua han
     */
    public function overdueworkordersAction()
    {
        $start      = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end        = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $loc        = $this->params->requests->getParam('m174_location', 0);
        $equipGroup = $this->params->requests->getParam('m174_group', 0);
        $equipID    = $this->params->requests->getParam('m174_equip', 0);
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $this->html->overOrders   = $mWorkorder->getOverDueIssueWorkOrders($mStart, $mEnd, $loc, $equipID);
        $this->html->deptid       = $this->_user->user_dept_id;
    }

    /**
     * >>> Vat tu duoi muc toi thieu
     */
    public function underminimumAction()
    {
        $mInventory        = new Qss_Model_Inventory_Inventory();
        $this->html->items = $mInventory->getCurrentInventory();
    }

    /**
     * >>> Thong so khong dat
     */
    public function dueparamsAction()
    {
        $mMonitor   = new Qss_Model_Maintenance_Equip_Monitor();
        $start      = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end        = $this->params->requests->getParam('m174_end', date('d-m-Y'));
        $equipGroup = $this->params->requests->getParam('m174_group', 0);
        $loc        = $this->params->requests->getParam('m174_location', 0);
        $equipID    = $this->params->requests->getParam('m174_equip', 0);

        $this->html->monitors = $mMonitor->getFailureMonitors(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , 0
            , 0
            , 0
            , $equipID
            , false
            , 0
            , $equipGroup
        );

    }

    public function rejectmonitorAction()
    {
        $mMoniter = new Qss_Model_Maintenance_Equip_Monitor();
        $params   = $this->params->requests->getParams();

        $mMoniter->rejectMonitor($params);

        echo Qss_Json::encode(array('error' => 0, 'message' => '', 'redirect' => null));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function createorderAction()
    {
        $equip       = $this->params->requests->getParam('Equip', 0);
        $dailyIFID   = $this->params->requests->getParam('DailyIFID', 0);
        $dailyIOID   = $this->params->requests->getParam('DailyIOID', 0);

        if(!$equip) return;

        $params['Equip'][]     = $equip;
        $params['DailyIFID'][] = $dailyIFID;
        $params['DailyIOID'][] = $dailyIOID;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->CreateOrdersFromFailure($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function createorder2Action()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->CreateOrderFromRequest($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
	public function saleeventAction()
    {
        $mMonitor   = new Qss_Model_Sale_Opportunity();
        $start      = $this->params->requests->getParam('m174_start', date('d-m-Y'));
        $end        = $this->params->requests->getParam('m174_end', date('d-m-Y'));
		$this->html->deptid   = $this->_user->user_dept_id;
        $this->html->trans = $mMonitor->getIncommingEvent($start, $end);
    }

}