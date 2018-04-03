<?php
/**
 * Class Button_M408Controller
 * Các button module Nhận hàng - M408
 */
class Button_M759Controller extends Qss_Lib_Controller
{
    public $m759_plans_next_start = '';

    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();         
    }

    public function assignIndexAction()
    {

    }

    public function assignSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M759->Assign->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
    
    public function updateCostAction()
    {
        $id = $this->params->requests->getParam('ifid');
        $chiphiphatsinh = $this->params->requests->getParam('chiphiphatsinh');
        $chiphithemgio = $this->params->requests->getParam('chiphithemgio');
        $ghichu = $this->params->requests->getParam('ghichu');
        
        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->Cost->Update($id,$chiphithemgio,$chiphiphatsinh,$ghichu);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);  
    }

    /**
     * @todo: Cần làm lại lấy phiễu xuất kho <không cần chọn kho như bây giờ>
     * Chỉ lấy mặt hàng theo phiếu xuất, so sánh mặt hàng nhập xuất với phiếu bảo trì
     */
    public function returnIndexAction()
    {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid = $ifid;
    }

    public function returnShowAction()
    {
        $ifid   = $this->params->requests->getParam('ifid', 0);

        $mWorkorder  = new Qss_Model_Maintenance_Workorder();
        $workorderLines = $mWorkorder->getMaterials($ifid);

        $this->html->workorder = $workorderLines;
    }

    public function returnSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->Material->Return($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function materialIndexAction()
    {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid = $ifid;
    }

    public function materialShowAction()
    {
//        $filter = $this->params->requests->getParam('m759_material_filter', 0);
//        $ifid   = $this->params->requests->getParam('ifid', 0);
//
//        $this->html->filter = $filter;
//        if($filter == 1)
//        {
//            $mInventory  = new Qss_Model_Inventory_Inventory();
//            $workorderLines = array();
//            $orderLines  = array();
//
//            // Lấy xuất kho
//            $output = $mInventory->getOutputByIFID($ifid);
//
//            // Lấy theo đơn hàng chỉ khi có Ref_DonHang
//            if(Qss_Lib_System::fieldExists('OXuatKho', 'Ref_SoDonHang') && $output && $output->Ref_SoDonHang)
//            {
//                $mOrder     = new Qss_Model_Purchase_Order();
//                $orderLines = $mOrder->getOrderLineByIOID($output->Ref_SoDonHang);
//            }
//
//            if($output && $output->Ref_PhieuBaoTri)
//            {
//                $mWorkorder  = new Qss_Model_Maintenance_Workorder();
//                $workorderLines = $mWorkorder->getMaterials(false, $output->Ref_PhieuBaoTri);
//            }
//
//            $this->html->workorder = $workorderLines;
//            $this->html->order     = $orderLines;
//        }
//        else
        {
            $mItem     = new Qss_Model_Master_Item();
            $perpage   = $this->params->requests->getParam('m759_material_perpage', 20);
            $page      = $this->params->requests->getParam('m759_material_page', 1);
            $total     = $mItem->countItems();
            $totalPage = ceil($total/$perpage);
            $items     = $mItem->getItemsLimit($page, $perpage);

            $this->html->items = $items;
            $this->html->pager = $this->views->UI->Pager(
                'm759_material_page'
                , 'm759_material_perpage'
                , $page
                , $perpage
                , $totalPage
                , 'm759_material_button.show()');
        }
    }

    // Lưu lại mặt hàng đã chọn
    public function materialSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->Material->SaveLines($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function plansIndexAction()
    {
        $this->headScript($this->params->requests->getBasePath() . '/js/extra/button/m759.js');
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';

        $location     = (int)$this->params->requests->getParam('location', 0);
        $mPlan        = new Qss_Model_Maintenance_Plan();

        $this->html->location      = $location;
        $this->html->locations     = $this->getLocations();
    }

    public function plansShowAction()
    {
        $start        = $this->params->requests->getParam('start', '');
        $end          = $this->params->requests->getParam('end', '');
        $location     = (int)$this->params->requests->getParam('location',  0);
        $equip        = (int)$this->params->requests->getParam('equip', 0);
        $nextBack     = $this->params->requests->getParam('nextBack', 1);
        $nextStart    = $this->params->requests->getParam('nextStart', $start);
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $end) == 1?$end:$nextStart;
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $start) == -1?$start:$nextStart;

        $this->html->plans     = ($start && $end)?$this->_addBasicPlan($start, $end, $nextStart, $nextBack, $location, $equip):array();
        $this->html->nextStart = $this->m759_plans_next_start;

    }

    public function plansSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Static->M759->CreateFromPlan($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    public function generalplansIndexAction()
    {
        $this->headScript($this->params->requests->getBasePath() . '/js/extra/button/m759.js');
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
        $ifid     = (int)$this->params->requests->getParam('general', 0);

        $this->html->generals = $this->getGeneralPlans();
        $this->html->ifid     = $ifid;
    }

    public function generalplansShowAction()
    {
        $ifid     = (int)$this->params->requests->getParam('general', 0);
        $mGeneral = new Qss_Model_Maintenance_GeneralPlans();
        $main     = $mGeneral->getGeneralPlanByIFID($ifid);
        $detail   = $mGeneral->getDetailPlanByGeneralIFID($ifid);

        $this->html->generals = $this->getGeneralPlans();
        $this->html->main     = $main;
        $this->html->detail   = $detail;
        $this->html->ifid     = $ifid;
    }

    public function generalplansSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->WorkOrder->CreateOrdersFromPlanDetails($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function checklistIndexAction()
    {
        // Luu y phan loai bao tri co the chuyen thanh radio
        $ifid         = $this->params->requests->getParam('ifid', 0);
        $oDinhKy      = array();
        $oKhongDinhKy = array();
        $mPhieuBaoTri = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mPhieuBaoTri->select('OPhanLoaiBaoTri.LoaiBaoTri, OPhieuBaoTri.Ref_LoaiBaoTri,OPhieuBaoTri.Ref_MoTa, Ref_MaThietBi, Ref_MaKhuVuc, Ref_BoPhan ');
        $mPhieuBaoTri->join('INNER JOIN OPhieuBaoTri ON OPhanLoaiBaoTri.IOID = OPhieuBaoTri.Ref_LoaiBaoTri');
        $mPhieuBaoTri->where(sprintf('OPhieuBaoTri.IFID_M759 = %1$d', $ifid));
        $oPhieuBaoTri = $mPhieuBaoTri->fetchOne();

        // Sau khi co loai bao tri ta kiem tra tiep de lay dieu kien so sanh, voi loai bao tri la dinh ky ta lay tu M724, voi loai su co ta lay tu M759
        if($oPhieuBaoTri)
        {
            if($oPhieuBaoTri->LoaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
            {
                // Lay theo M724, load thẳng công việc không có điều kiện lọc
                $mDinhKy = Qss_Model_Db::Table('OCongViecBT');
                $mDinhKy->select('OCongViecBT.*');
                $mDinhKy->join('INNER JOIN OBaoTriDinhKy ON OCongViecBT.IFID_M724 = OBaoTriDinhKy.IFID_M724');
                $mDinhKy->join(sprintf('LEFT JOIN (SELECT * FROM OCongViecBTPBT WHERE IFID_M759 = %1$s) AS CongViecBTPBT ON IFNULL(OCongViecBT.MoTa, "") = IFNULL(CongViecBTPBT.MoTa, "")', $ifid));
                $mDinhKy->where(sprintf('OBaoTriDinhKy.IOID = %1$d', (int)$oPhieuBaoTri->Ref_MoTa));
                $mDinhKy->where($mDinhKy->ifnullNumber('CongViecBTPBT.IOID', 0));
                $oDinhKy = $mDinhKy->fetchAll();
            }
            else
            {
                // Lấy theo M759, chọn các phiếu cũ
                $mPhieuTruoc = Qss_Model_Db::Table('OPhieuBaoTri');
                if($oPhieuBaoTri->Ref_MaThietBi)
                {
                    $mPhieuTruoc->where($mPhieuTruoc->ifnullNumber('OPhieuBaoTri.Ref_MaThietBi', (int)$oPhieuBaoTri->Ref_MaThietBi));
                    $mPhieuTruoc->where($mPhieuTruoc->ifnullNumber('OPhieuBaoTri.Ref_BoPhan', (int)$oPhieuBaoTri->Ref_BoPhan));
                }
                elseif($oPhieuBaoTri->Ref_MaKhuVuc)
                {
                    $mPhieuTruoc->where($mPhieuTruoc->ifnullNumber('OPhieuBaoTri.Ref_MaKhuVuc', (int)$oPhieuBaoTri->Ref_MaKhuVuc));
                }
                $mPhieuTruoc->where($mPhieuTruoc->ifnullNumber('Ref_LoaiBaoTri', (int)$oPhieuBaoTri->Ref_LoaiBaoTri));
                $mPhieuTruoc->where(sprintf('OPhieuBaoTri.IFID_M759 != %1$d', $ifid));
                $mPhieuTruoc->orderby('NgayBatDau DESC, SoPhieu DESC');
                $oKhongDinhKy = $mPhieuTruoc->fetchAll();
            }
        }

        $this->html->deptid        = $this->_user->user_dept_id;
        $this->html->ifid          = $ifid;
        $this->html->strLoaiBaoTri = $oPhieuBaoTri->LoaiBaoTri;
        $this->html->oDinhKy       = $oDinhKy;
        $this->html->oKhongDinhKy  = $oKhongDinhKy;
    }

    public function checklistShowAction()
    {
        $ifidSaoChep = $this->params->requests->getParam('ifidPhieuBaoTri', 0);
        $ifidHienTai = $this->params->requests->getParam('ifid', 0);

        $mKhongDinhKy = Qss_Model_Db::Table('OCongViecBTPBT AS v1');
        $mKhongDinhKy->select('v1.*');
        $mKhongDinhKy->join(sprintf('LEFT JOIN (SELECT * FROM OCongViecBTPBT WHERE IFID_M759 = %1$s) AS v2 ON IFNULL(v1.MoTa, "") = IFNULL(v2.MoTa, "")', $ifidHienTai));
        $mKhongDinhKy->where(sprintf('v1.IFID_M759 = %1$d', $ifidSaoChep));
        $mKhongDinhKy->where($mKhongDinhKy->ifnullNumber('v2.IOID', 0));
        $oKhongDinhKy = $mKhongDinhKy->fetchAll();

        $this->html->oKhongDinhKy  = $oKhongDinhKy;
    }

    public function checklistSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M759->Checklist->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getGeneralPlans()
    {
        $common  = new Qss_Model_Maintenance_GeneralPlans();
        $emps    = $common->getPlansByStatus(3);
        $ret     = array();

        foreach($emps as $item) {
            $ret[$item->IFID_M838] = "{$item->Ma} - {$item->Ten} (".Qss_Lib_Date::mysqltodisplay($item->NgayBatDau)
                ." - ".Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc) . ')';
        }
        return $ret;
    }

    public function getLocations()
    {
        $mLocation = new Qss_Model_Maintenance_Location();
        $tag       = $this->params->requests->getParam('tag', '');
        $locations = $mLocation->getLocationByCurrentUser();
        $retval    = array();

        foreach($locations as $item)
        {
            $retval[$item->IOID] = str_repeat('&nbsp;', (($item->level -1)*3)) . "{$item->MaKhuVuc} - {$item->Ten}";
        }

        return $retval;
    }



    private function _addBasicPlan($startDate, $endDate, $nextStart, $nextBack, $locIOID = 0, $equip = 0) {
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
            $plans 	   = $basicplan->getPlansByDate($date, $locIOID, 0, 0, 0, 0, $equip);

            foreach ($plans as $plan) {
                $plan->NgayKetThuc = ($plan->NgayKetThuc == '0000-00-00')?'':$plan->NgayKetThuc;
                $khuvuc            = (int) $plan->Ref_KhuVuc;
                $thietbi           = (int) $plan->Ref_MaThietBi;
                $bophan            = (int) $plan->Ref_BoPhan;
                $chuky             = (int) $plan->ChuKyIOID;

//                if(($plan->CanCu == 1 || $plan->CanCu == 2) && isset($arrChiSo[$khuvuc][$thietbi][$bophan][$chuky])) {
//
//                }
//                else {
//
//                    $retval[++$i]     = $plan;
//                    $retval[$i]->Date = $date;
//                    $count            = ($nextBack)?($count+1):($count-1);
//                }
                $retval[++$i]     = $plan;
                $retval[$i]->Date = $date;
                $count            = ($nextBack)?($count+1):($count-1);

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

    public function plansEquipAction()
    {
        $location     = (int)$this->params->requests->getParam('location', 0);
        $this->html->location      = $location;
    }

    public function equipmentsAction()
    {
        $mLocation = new Qss_Model_Maintenance_Equip_List();
        $tag       = $this->params->requests->getParam('tag', '');
        $location  = $this->params->requests->getParam('location', 0);
        $locations = $mLocation->getEquipByLocation($location, $tag);
        $retval    = array();

        foreach ($locations as $item)
        {
            if($item->MaThietBi)
            {
                $display  = "{$item->MaThietBi} - {$item->TenThietBi}";
                $retval[] = array('id'=>$item->IOID, 'value'=>$display);
            }
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }
 	public function employeePlanAction()
    {
    	$this->headLink($this->params->requests->getBasePath() . '/css/extra/button/m759.css');
    	$this->headScript($this->params->requests->getBasePath() . '/js/extra/button/m759.js');
    	$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$worktype = $this->params->requests->getParam('worktype',0);
		if(!$worktype)
		{
			$this->html->content = $this->views->Button->M759->Employee->Plan->Workorder($this->_user,$month,$year,$workcenter);
		}
		else 
		{
			$this->html->content = $this->views->Button->M759->Employee->Plan->Task($this->_user,$month,$year,$workcenter);
		}
		$this->html->month = $month;
		$this->html->year = $year;
		$model = new Qss_Model_M125_Workcenter();
		$this->html->workcenter = $workcenter;
		$this->html->workcenters = $model->getWorkCenters();

    }
	public function employeeReloadAction()
    {
    	$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$workcenter = $this->params->requests->getParam('workcenter',0);
		$worktype = $this->params->requests->getParam('worktype',0);
		if(!$worktype)
		{
			$this->html->content = $this->views->Button->M759->Employee->Plan->Workorder($this->_user,$month,$year,$workcenter);
		}
		else 
		{
			$this->html->content = $this->views->Button->M759->Employee->Plan->Task($this->_user,$month,$year,$workcenter);
		}
		$this->html->month = $month;
		$this->html->year = $year;
    }
	public function employeeDateAction()
    {
    	$model = new Qss_Model_M759_Employee();
    	$eid = $this->params->requests->getParam('eid',0);
		$date = $this->params->requests->getParam('date');
		$this->html->workorders = $model->getWorkOrderByDate($eid,$date);
		$this->html->eid = $eid;
		$this->html->date = $date;
		$table = Qss_Model_Db::Table('ODanhSachNhanVien');
		$table->where(sprintf('IOID=%1$d',$eid));
		$this->html->employee = $table->fetchOne(); 
    }
	public function employeeDateWorkorderAction()
    {
    	$model = new Qss_Model_M759_Employee();
		$date = $this->params->requests->getParam('date');
		$this->html->workorders = $model->getUnassignedWO($date);
		$this->html->date = $date;
    }
	public function employeeDateWorkorderSaveAction()
    {
    	//$model = new Qss_Model_M759_Employee();
		$params = $this->params->requests->getParams();
		//$service = $this->services->Button->M759->Employee->Plan->Workorder->Save($params);
        //echo $service->getMessage();
    	$error = false;
    	$message = '';
		$eid = $params['eid'];
		$date = $params['date'];
		$workorder = $params['workorder'];
		foreach($workorder as $ifid)
		{
			$arrUpdate = array('OPhieuBaoTri'=>array(0=>array('ifid'=>$ifid,
															'NgayBatDauDuKien'=>$date,
															'NguoiThucHien'=> (int)$eid
															)));
			$service = $this->services->Form->Manual('M759',$ifid,$arrUpdate);
			if($service->isError())
			{
				$error = true;
				$message .= $service->getMessage(Qss_Service_Abstract::TYPE_TEXT).'<br>';
			}
		}
		$arr = array('error'=>false);
		if($error)
		{
			$arr = array('error'=>true,'message'=>$message);
		}
		echo Qss_Json::encode($arr);
		$this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
	public function employeeDateWorkorderCancelAction()
    {
		$ifid = $this->params->requests->getParam('ifid',0);
    	$arrUpdate = array('OPhieuBaoTri'=>array(0=>array('ifid'=>$ifid,
															'NguoiThucHien'=> (int)0
															)));
		$service = $this->services->Form->Manual('M759',$ifid,$arrUpdate);
		echo $service->getMessage();
		$this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
	public function employeeDateTaskAction()
    {
    	$model = new Qss_Model_M759_Employee();
		$date = $this->params->requests->getParam('date');
		$this->html->workorders = $model->getUnassignedTask($date);
		$this->html->date = $date;
    }
	public function employeeDateTaskSaveAction()
    {
    	//$model = new Qss_Model_M759_Employee();
		$params = $this->params->requests->getParams();
		//$service = $this->services->Button->M759->Employee->Plan->Workorder->Save($params);
        //echo $service->getMessage();
    	$error = false;
    	$message = '';
		$eid = $params['eid'];
		$date = $params['date'];
		$task = $params['task'];
		foreach($task as $ioid)
		{
			$ifid = $params['workorder_'.$ioid];
			$arrUpdate = array('OPhieuBaoTri'=>array(0=>array('ifid'=>$ifid,'NgayBatDauDuKien'=>$date)),
								'OCongViecBTPBT'=>array(0=>array('ioid'=>$ioid,'NguoiThucHien'=> (int)$eid)));
			$service = $this->services->Form->Manual('M759',$ifid,$arrUpdate);
			if($service->isError())
			{
				$error = true;
				$message .= $service->getMessage(Qss_Service_Abstract::TYPE_TEXT).'<br>';
			}
		}
		$arr = array('error'=>false);
		if($error)
		{
			$arr = array('error'=>true,'message'=>$message);
		}
		echo Qss_Json::encode($arr);
		$this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
	public function employeeDateTaskCancelAction()
    {
		$ifid = $this->params->requests->getParam('ifid',0);
		$ioid = $this->params->requests->getParam('ioid',0);
    	$arrUpdate = array('OPhieuBaoTri'=>array(0=>array('ifid'=>$ifid)),
							'OCongViecBTPBT'=>array(0=>array('ioid'=>$ioid,
															'NguoiThucHien'=> (int)0
															)));
		$service = $this->services->Form->Manual('M759',$ifid,$arrUpdate);
		echo $service->getMessage();
		$this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
	public function employeeTaskdateAction()
    {
    	$model = new Qss_Model_M759_Employee();
    	$eid = $this->params->requests->getParam('eid',0);
		$date = $this->params->requests->getParam('date');
		$this->html->workorders = $model->getTaskByDate($eid,$date);
		$this->html->eid = $eid;
		$this->html->date = $date;
		$table = Qss_Model_Db::Table('ODanhSachNhanVien');
		$table->where(sprintf('IOID=%1$d',$eid));
		$this->html->employee = $table->fetchOne(); 
    }
}