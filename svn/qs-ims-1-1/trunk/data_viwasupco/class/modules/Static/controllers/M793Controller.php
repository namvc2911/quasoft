<?php
class Static_M793Controller extends Qss_Lib_Controller
{
    public $plans     = array();
    public $materials = array();
    public $tasks     = array();
    public $date      = array();

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}

	public function indexAction()
	{

	}

	public function showAction()
	{
	    $templateNo  = $this->params->requests->getParam('title', 0);
		$sDate       = $this->params->requests->getParam('sdate', 0);
		$eDate       = $this->params->requests->getParam('edate', 0);
		$locIOID     = $this->params->requests->getParam('loc_ioid', 0);
		$eqGroupIOID = $this->params->requests->getParam('eq_group_ioid', 0);
		$eqTypeIOID  = $this->params->requests->getParam('eq_type_ioid', 0);
		$mTypeIOID   = $this->params->requests->getParam('maint_type_ioid', 0);
		$mSDate      = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate      = Qss_Lib_Date::displaytomysql($eDate);
        $this->_getReportData($mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID, $mTypeIOID);


        $inOneMonth  = '';

        if(
            ( (int)date('d', strtotime($sDate)) == 1 )
            && ( (int)date('d', strtotime($eDate)) == (int)date('t', strtotime($eDate)) )
            && ( (int)date('m', strtotime($sDate)) == (int)date('m', strtotime($eDate)) )
        ) {
            $inOneMonth = date('m-Y', strtotime($sDate));
        }


		$this->html->date       = $this->date;
        $this->html->plans      = $this->plans;
        $this->html->materials  = $this->materials;
        $this->html->tasks      = $this->tasks;
		$this->html->start      = $sDate;
		$this->html->end        = $eDate;
		$this->html->templateNo = $templateNo;
		$this->html->inOneMonth = $inOneMonth;

	}

	private function _getReportData($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $mTypeIOID= 0)
    {
        $customModel = new Qss_Model_Maintenance_Viwasupcoplan();
        $model       = new Qss_Model_Maintenance_Plan();
        // $plans    = $customModel->getPlansOrderByLocationAndType($locIOID, $eqGroupIOID, $eqTypeIOID, $mTypeIOID);
        $tmpEnd      = date_create($mEDate);
        $ifids       = array();
        $tmpStart    = date_create($mSDate);
        $mLocation   = new Qss_Model_M720_Location();
        $mEquiptype  = new Qss_Model_M770_Equiptype();
        $highestLocs = $mLocation->getLocationDontHasParent();
        $equipTypes  = $mEquiptype->getEquipTypes();
        $retval      = array();

        // Dùng để sắp xếp theo khu vực và loại thiết bị trước theo lft
        foreach($highestLocs as $loc) {
            foreach($equipTypes as $type) {
                $key = (int)$loc->IOID . '-' . (int)$type->IOID;
                $retval[$key] = array();
            }
        }

        while($tmpStart <= $tmpEnd) {
            $plans = $customModel->getPlansOrderByLocationAndType($tmpStart->format('Y-m-d'), $locIOID, $eqTypeIOID, $eqGroupIOID, $mTypeIOID);

            foreach($plans as $plan) {
                $key = (int)$plan->KhuVucIOID . '-' . (int)$plan->LoaiThietBiIOID;
                $plan->NgayBaoDuong = $tmpStart->format('Y-m-d');
                $retval[$key][]                 = $plan;
                $ifids[]                        = $plan->IFID_M724;
                $this->date[$plan->IFID_M724][] = $tmpStart->format('Y-m-d');
            }

            $tmpStart = Qss_Lib_Date::add_date($tmpStart, 1);
        }

        // echo '<pre>'; print_r($retval); die;

//        foreach ($plans as $mp) {
//            $tmpStart = date_create($mSDate);
//            if(!isset($this->plans[$mp->IFID_M724])) { // Lay tat ra kiem tra lai theo date
//                $this->plans[$mp->IFID_M724] = $mp;
//                $ifids[]                     = $mp->IFID_M724;
//            }
//
//            while($tmpStart <= $tmpEnd) {
//                $day     = $tmpStart->format('d');
//                $month   = $tmpStart->format('m');
//                $weekday = $tmpStart->format('w');
//                $date    = $tmpStart->format('Y-m-d');
//
//
//
//                if(
//                    ($mp->KyBaoDuong == Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY && Qss_Lib_Date::diffTime($mp->NgayBatDau, $date, 'D') % $mp->LapLai == 0)
//                    || ($mp->KyBaoDuong == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY && $day == $mp->Ngay  && Qss_Lib_Date::diffTime($mp->NgayBatDau, $date, 'MO') % $mp->LapLai == 0)
//                    || ($mp->KyBaoDuong == Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY  && $weekday == $mp->NgayThu && Qss_Lib_Date::diffTime($mp->NgayBatDau, $date, 'W') % $mp->LapLai == 0)
//                    || ($mp->KyBaoDuong == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY  && $day == $mp->Ngay && $month == $mp->Thang && Qss_Lib_Date::diffTime($mp->NgayBatDau, $date, 'Y') % $mp->LapLai == 0)
//                ) {
//                    $this->date[$mp->IFID_M724][] = $date;
//                }
//                $tmpStart = Qss_Lib_Date::add_date($tmpStart, 1);
//            }
//        }



        // Loại bỏ các ngày không có kế hoạch
        foreach ($retval as $key=>$array) {
            if(!count($array)) {
                unset($retval[$key]);
            }
        }

        foreach($retval as $key=>$array) {
            if(!count($array)) {
                unset($retval[$key]);
            }
            else {
                foreach ($array as $item) {
                    $this->plans[] = $item;
                }
            }
        }

//        echo '<pre>'; print_r( $this->plans) ;die;

//        foreach ($this->plans as $ifidKey=>$plan) {
//            if(!isset($this->date[$ifidKey])) {
//                unset($this->plans[$ifidKey]);
//            }
//        }


        $materials = $model->getMaterialsByPlanIFID($ifids);
        $tasks     = $model->getTasksByPlanIFID($ifids);



        // Bộ phận (BoPhan)	Nội dung công tác bảo dưỡng	(MoTa)	Vật tư bảo dưỡng Định mức vật tư	TenVatTu SoLuong DonViTinh		Số người Bảo Dưỡng (NhanCong)
        foreach($tasks as $m) {
            $key = (int)$m->IOID;

            if(!isset($this->tasks)) {
                $this->tasks = array();
            }

            if(!isset($this->tasks[$m->IFID])) {
                $this->tasks[$m->IFID] = array();
            }
			$this->tasks[$m->IFID][$key]   = new stdClass();
            $this->tasks[$m->IFID][$key]->BoPhan   = $m->BoPhan;
            $this->tasks[$m->IFID][$key]->MoTa     = $m->MoTa;
            $this->tasks[$m->IFID][$key]->NhanCong = $m->NhanCong;
        }

        $i = 0;
        foreach($materials as $m) {
            $key = (int)$m->Ref_CongViec;
            if(!isset($this->tasks[$m->IFID][$key])) {
                $this->tasks[$m->IFID][$key] = new stdClass();
                $this->tasks[$m->IFID][$key]->BoPhan    = '';
                $this->tasks[$m->IFID][$key]->MoTa      = '';
                $this->tasks[$m->IFID][$key]->NhanCong  = '';
            }

            if(!isset($this->tasks[$m->IFID][$key]->ArrVatTu)) {
                $this->tasks[$m->IFID][$key]->ArrVatTu = array();
            }

            $this->tasks[$m->IFID][$key]->ArrVatTu[$i] = new stdClass();
            $this->tasks[$m->IFID][$key]->ArrVatTu[$i]->TenVatTu  = $m->TenVatTu;
            $this->tasks[$m->IFID][$key]->ArrVatTu[$i]->SoLuong   = $m->SoLuong;
            $this->tasks[$m->IFID][$key]->ArrVatTu[$i]->DonViTinh = $m->DonViTinh;
            $i++;
        }
        
    }
}

?>