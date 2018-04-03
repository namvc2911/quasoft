<?php
class Static_M838Controller extends Qss_Lib_Controller
{
    protected $user;

    public function init()
    {
        parent::init();
        $this->user = Qss_Register::get('userinfo');

        $this->headScript($this->params->requests->getBasePath() . '/js/import-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
    }

    public function indexAction()
    {

    }

    /**
     * In cac ke hoach tong the theo nam
     */
    public function generalIndexAction()
    {
        // $year  = $this->params->requests->getParam('year', date('Y'));
        $mPlan = new Qss_Model_Maintenance_GeneralPlans();
        $year  = '';

        $this->html->year    = $year;
        $this->html->plans   = $mPlan->getGeneralPlansByYear($year);
        $this->html->deptid  = $this->user->user_dept_id;
        $this->html->calType = Qss_Lib_System::getFieldRegx('OKeHoachTongThe', 'LoaiLich');
    }

    public function generalDeleteAction()
    {
        $ifid  = $this->params->requests->getParam('ifid', 0);
        $ifids = array();

        if($ifid)
        {
            $ifids[] = $ifid;
        }

        if ( $this->params->requests->isAjax() )
        {
            $service = $this->services->Maintenance->GeneralPlan->Delete($ifids);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function generalDuplicateAction()
    {
        $params  = $this->params->requests->getParams();
        if ( $this->params->requests->isAjax() )
        {
            $service = $this->services->Maintenance->GeneralPlan->Duplicate($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function generalSaveAction()
    {
        $params  = $this->params->requests->getParams();

        if ( $this->params->requests->isAjax() )
        {
            $service = $this->services->Maintenance->GeneralPlan->Update($params);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Chi tiet mot ke hoach tong the
     */
    public function detailIndexAction()
    {
        $mPlan = new Qss_Model_Maintenance_GeneralPlans();
        $ifid  = $this->params->requests->getParam('ifid', 0);

        $this->html->generalPlan = $mPlan->getGeneralPlanByIFID($ifid);
        $this->html->ifid        = $ifid;
        $this->html->deptid      = $this->user->user_dept_id;
        $this->html->userid      = $this->user->user_id;
    }

    public function detailShowAction()
    {
        $mPlan = new Qss_Model_Maintenance_GeneralPlans();
        $ifid  = $this->params->requests->getParam('ifid', 0);
        $location  = $this->params->requests->getParam('location', 0);
        $equiptype  = $this->params->requests->getParam('equiptype', 0);

        $this->html->generalPlan = $mPlan->getGeneralPlanByIFID($ifid);
        $this->html->detailPlans = $mPlan->getDetailPlanByGeneralIFID($ifid, $location, $equiptype);
        $this->html->ifid        = $ifid;
        $this->html->deptid      = $this->user->user_dept_id;
    }

    public function detailGeneralAction()
    {
        $mPlan = new Qss_Model_Maintenance_GeneralPlans();
        $ifid  = $this->params->requests->getParam('ifid', 0);

        $this->html->generalPlan = $mPlan->getGeneralPlanByIFID($ifid);
        $this->html->ifid        = $ifid;
        $this->html->deptid      = $this->user->user_dept_id;
        $this->html->calType     = Qss_Lib_System::getFieldRegx('OKeHoachTongThe', 'LoaiLich');
    }

    public function detailFailureAction()
    {
        $mMonitor            = new Qss_Model_Maintenance_Equip_Monitor();
        $start               = $this->params->requests->getParam('start', date('d-m-Y', strtotime("-7 days")));
        $end                 = $this->params->requests->getParam('end', date('d-m-Y'));
        $ifid                = $this->params->requests->getParam('ifid', 0);
        $ioid                = $this->params->requests->getParam('ioid', 0);
        $failure             = array();

        if($start && $end)
        {
            $failure = $mMonitor->getFailureMonitors(
                Qss_Lib_Date::mysqltodisplay($start)
                , Qss_Lib_Date::mysqltodisplay($end)
            );
        }

        $this->html->start   = Qss_Lib_Date::mysqltodisplay($start);
        $this->html->end     = Qss_Lib_Date::mysqltodisplay($end);
        $this->html->ifid    = $ifid;
        $this->html->ioid    = $ioid;
        $this->html->rStatus = Qss_Lib_System::getFieldRegx('ONhatTrinhThietBi', 'TinhTrang');
        $this->html->failure = $failure;
    }

    public function detailCreatefromfailureAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->GeneralPlan->CreateDetailFromFailure($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    public function detailPlanAction()
    {
        $start        = $this->params->requests->getParam('start', date('d-m-Y'));
        $end          = $this->params->requests->getParam('end', date('d-m-Y', strtotime("+7 days")));
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $ioid         = $this->params->requests->getParam('ioid', 0);

        $cStartDate = date_create(Qss_Lib_Date::displaytomysql($start));
        $cEndDate   = date_create(Qss_Lib_Date::displaytomysql($end));
        $tStartDate = $cStartDate;
        $arrChiSo   = array();
        $retval     = array();

        if($start && $end)
        {
            while ($tStartDate <= $cEndDate)
            {
                $date      = $tStartDate->format('Y-m-d');
                $basicplan = Qss_Model_Maintenance_Plan::createInstance();
                $plans     = $basicplan->getPlansByDate($date);

                // echo '<pre>'; print_r($plans);

                foreach ($plans as $plan)
                {
                    $plan->NgayKetThuc = ($plan->NgayKetThuc == '0000-00-00')?'':$plan->NgayKetThuc;
                    $khuvuc            = (int) $plan->Ref_KhuVuc;
                    $thietbi           = (int) $plan->Ref_MaThietBi;
                    $bophan            = (int) $plan->Ref_BoPhan;
                    $chuky             = (int) $plan->ChuKyIOID;

                    if(($plan->CanCu == 1 || $plan->CanCu == 2) && isset($arrChiSo[$khuvuc][$thietbi][$bophan][$chuky]))
                    {

                    }
                    else
                    {
                        $plan->NgayKeHoach = $date;
                        $retval[]          = $plan;
                    }

                    if($plan->CanCu == 1 || $plan->CanCu == 2)
                    {
                        $arrChiSo[$khuvuc][$thietbi][$bophan][$chuky] = 1;
                    }
                }
                $tStartDate = Qss_Lib_Date::add_date($tStartDate, 1);
            }
        }


        // echo '<pre>'; print_r($retval); die;

        $this->html->start   = Qss_Lib_Date::mysqltodisplay($start);
        $this->html->end     = Qss_Lib_Date::mysqltodisplay($end);
        $this->html->ifid    = $ifid;
        $this->html->ioid    = $ioid;
        $this->html->plans   = $retval;
    }

    public function detailCreatefromplanAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->GeneralPlan->CreateDetailFromPlan($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function detailWorkorderAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->CreateOrdersFromPlanDetail($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}