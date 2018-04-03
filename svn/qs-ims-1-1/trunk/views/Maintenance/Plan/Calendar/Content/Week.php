<?php
class Qss_View_Maintenance_Plan_Calendar_Content_Week extends Qss_View_Abstract
{
    public $plans       = array();
    public $calendar    = array();
    public $orders      = array();
    public $iPlan       = 0;
    public $spcialDates = array();
    public $arrDVBT     = array();
    public $arrNguoiThucHien = array();

    public function __doExecute ($user,$w,$m,$y,$workcenter,$responseid)
    {
        if($w == 53 && $m == 1)
        {
            $y--;
        }

        $model     = new Qss_Model_Maintenance_Calendar();
        $sonar     = new Qss_Model_Calendar_Solar();
        $startd    = Qss_Lib_Date::getDateByWeek($w,$y);
        $startdate = $startd->format('Y-m-d');
        $enddate   = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');

        $this->html->data       = $this->_getCalendar($startdate, $enddate,$workcenter,$responseid);
        $this->html->prev       = $sonar->adjustWeek($w - 1, $m, $y);
        $this->html->next       = $sonar->adjustWeek($w + 1, $m, $y);
        $this->html->startdate1 = $startd;
        $this->html->startdate  = $startdate;
        $this->html->enddate    = $enddate;
        $this->html->donvibaotri   = $this->arrDVBT;
        $this->html->nguoithuchien = $this->arrNguoiThucHien;
    }

    /**
     * Lấy dữ liệu kế hoạch bảo trì sắp xếp theo ngày trong tuần
     * @param date $startDate <YYYY-mm-dd> Ngày đầu tuần
     * @param date $endDate <YYYY-mm-dd> Ngày cuối tuần
     * @return object Mảng kế hoạch bảo trì [hàng = thứ tự sắp xếp chèn $i][cột = cột ngày $j]
     */
    private function _getCalendar($startDate, $endDate,$workcenter,$responseid)
    {
        // Phieu bao tri
        //$this->_getExistsOrder($startDate, $endDate);

        // Them ke hoach bao tri dinh ky
        $this->_addBasicPlan($startDate, $endDate,$workcenter,$responseid);


        // Sap xep ke hoach de in
        $this->_sortPlans($startDate, $endDate);

        // echo '<Pre>'; print_r($this->calendar); die;

        ksort($this->calendar);
        return $this->calendar;
    }


    /**
     * Lấy mảng trạng thái của phiếu bt theo máy, loại bảo trì, bộ phận bảo trì(Đối tượng chính) theo ngày, ngày trong
     * tuần + năm, tháng + năm, năm.
     * @param date $startDate <YYYY-mm-dd> Ngày đầu tuần
     * @param date $endDate <YYYY-mm-dd> Ngày cuối tuần
     */
    private function _getExistsOrder($startDate, $endDate)
    {
        $orderModel = new Qss_Model_Maintenance_Workorder();
        ///$this->orders = $orderModel->getWorkOrdersInRange($startDate, $endDate);

        // Phieu bao tri theo ngay, thiet bi, bo phan, loai bao tri
        $workorders = $orderModel->getOrders($startDate, $endDate);

        foreach ($workorders as $order)
        {
            $tStartDate = date_create($order->NgayBatDau);
            $date       = $tStartDate->format('Y-m-d');
            $day        = @(int)$tStartDate->format('d');
            $month      = @(int)$tStartDate->format('m');
            $weekno       = @(int)$tStartDate->format('W');
            $year       = @(int)$tStartDate->format('Y');
            $code       = @(int)$order->Ref_MaThietBi.'_'.@(int)$order->Ref_LoaiBaoTri.'_'.@(int)$order->Ref_BoPhan;

            $this->orders[$code][$date]            = 1;
            $this->orders[$code]['w_'.$weekno.'_'.$year]  = 1;
            $this->orders[$code]['m_'.$month.'_'.$year] = 1;
            $this->orders[$code][$year]            = 1;
        }
    }

    private function _checkWorkorderExists($code, $period, $date)
    {
        if(!$period) return false;

        $tStartDate = date_create($date);
        $date       = $tStartDate->format('Y-m-d');
        $day        = @(int)$tStartDate->format('d');
        $month      = @(int)$tStartDate->format('m');
        $weekno       = @(int)$tStartDate->format('W');
        $year       = @(int)$tStartDate->format('Y');
        $retval     = false;

        switch ($period)
        {
            case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
                if(isset($this->orders[$code][$date])) // Has plan
                {
                    $retval = true;
                }
                break;

            case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
                if(isset($this->orders[$code]['w_'.$weekno.'_'.$year])) // Has plan
                {
                    $retval = true;
                }
                break;

            case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
                if(isset($this->orders[$code]['m_'.$month.'_'.$year])) // Has plan
                {
                    $retval = true;
                }
                break;

            case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
                if(isset($this->orders[$code][$year])) // Has plan
                {
                    $retval = true;
                }
                break;
        }
        return $retval;
    }

    private function _checkDateHasPlan($date, $plan)
    {
        if(count((array)$plan) == 0 || !$date) return false;

        $return = false;

        $cDate =  date_create($date);
        $day   = @(int)$cDate->format('d');
        $month = @(int)$cDate->format('m');
        $wday  = @(int)$cDate->format('w');
        $date  = $cDate->format('Y-m-d');
        $code  = @(int)$plan->Ref_MaThietBi.'_'.@(int)$plan->Ref_LoaiBaoTri.'_'.@(int)$plan->Ref_BoPhan;

        if(
            (!isset($this->spcialDates[$plan->Ref_MaThietBi])
                || !in_array($date, $this->spcialDates[$plan->Ref_MaThietBi]))
            && (!$plan->NgayBatDau
                || Qss_Lib_Date::compareTwoDate($date,$plan->NgayBatDau ) >= 0 )
            && (!$plan->NgayKetThuc
                || Qss_Lib_Date::compareTwoDate($date,$plan->NgayKetThuc) <= 0)
            && !$this->_checkWorkorderExists($code, $plan->MaKy, $date)
        )
        {

            $diffFromStart = ($plan->NgayBatDau)?Qss_Lib_Date::diffTime($plan->NgayBatDau, $date, 'D'):0;
            $interval      = (@(int)$plan->LapLai > 1)?$plan->LapLai:1;
            $surplus       = $diffFromStart%$interval;

            switch ($plan->MaKy)
            {
                case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
                    if($surplus == 0) // Has plan
                    {
                        $return = true;
                    }
                    break;

                case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
                    if($plan->Ngay == $day  && $plan->Thang == $month && $surplus == 0) // Has plan
                    {
                        $return = true;
                    }
                    break;

                case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
                    if($plan->GiaTriThu == $wday  && $surplus == 0) // Has plan
                    {
                        $return = true;
                    }
                    break;

                case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
                    if($plan->Ngay == $day && $plan->Thang == $month  && $surplus == 0) // Has plan
                    {
                        $return = true;
                    }
                    break;
            }

        }

        return $return;
    }


    private function _addPlan($plan, $tStartDate)
    {
        $numOfDate     = (@(int)$plan->SoNgay > 1)?$plan->SoNgay:1;
        $numOfDatePlus = $numOfDate - 1;
        $retval        = new stdClass();
        Qss_Lib_Util::copyObject($plan,$retval);

        $date          = $tStartDate->format('Y-m-d');
        $this->plans[$this->iPlan] = $retval;
        $this->plans[$this->iPlan]->StartDate = $date;
        $this->plans[$this->iPlan]->EndDate   = date('Y-m-d', strtotime($date . " +".$numOfDatePlus." days"));
        $this->plans[$this->iPlan]->Amount    = Qss_Lib_Date::diffTime($this->plans[$this->iPlan]->StartDate, $this->plans[$this->iPlan]->EndDate, 'D');
        $this->iPlan++;

        $t2StartDate = $tStartDate;

        if($numOfDatePlus > 0)
        {
            for($i = $numOfDatePlus;$i == 0; $i--)
            {
                $t2StartDate = Qss_Lib_Date::diff_date($t2StartDate, 1);
                $t2Date      = $t2StartDate->format('Y-m-d');

                if($this->_checkDateHasPlan($t2Date, $plan)
                    && (!isset($this->spcialDates[$plan->Ref_MaThietBi])
                        || !in_array($t2Date, $this->spcialDates[$plan->Ref_MaThietBi])))
                {

                    $this->arrDVBT[$plan->Ref_MaDVBT] = $plan->TenDVBT;
                    $this->arrNguoiThucHien[$plan->Ref_NguoiThucHien] = $plan->NguoiThucHien;

                    $this->plans[$this->iPlan] = $plan;
                    $this->plans[$this->iPlan]->StartDate = $t2Date;
                    $this->plans[$this->iPlan]->EndDate   = date('Y-m-d', strtotime($t2Date . " +".$numOfDatePlus." days"));
                    $this->plans[$this->iPlan]->Amount    = Qss_Lib_Date::diffTime($this->plans[$this->iPlan]->StartDate, $this->plans[$this->iPlan]->EndDate, 'D');
                    $this->iPlan++;
                }
            }
        }
    }


    private function _addBasicPlan($startDate, $endDate,$workcenter,$responseid)
    {
        // Chuan bi du lieu loop theo ngay
        $ob         = new Qss_Model_Maintenance_Equip_Operation();
        $cStartDate = date_create($startDate);
        $cEndDate   = date_create($endDate);
        $tStartDate = $cStartDate;

        $workOrderModel = new Qss_Model_Maintenance_Workorder();
        $equipModel     = new Qss_Model_Maintenance_Equipment();

        $arrChiSo = array();
        while ($tStartDate <= $cEndDate)
        {
            $date  = $tStartDate->format('Y-m-d');
            $day   = @(int)$tStartDate->format('d');
            $month = @(int)$tStartDate->format('m');
            $wday  = @(int)$tStartDate->format('w');

            $basicplan = Qss_Model_Maintenance_Plan::createInstance();

            // Toan bo ke hoach bao tri
            //$basicplan->setFilterByUserOfWorkCenter();
            //$basicplan->setExcludeExistOrders($date);
            //$basicplan->setFilterByDate($date);
            //$basicplan->setFilterByLocIOID($locIOID);

            $plans 	= $basicplan->getAllPlansByDate($date, 0, $workcenter);
            foreach ($plans as $plan)
            {
                $plan->NgayKetThuc = ($plan->NgayKetThuc == '0000-00-00')?'':$plan->NgayKetThuc;
                $code  = @(int)$plan->Ref_MaThietBi.'_'.@(int)$plan->Ref_LoaiBaoTri.'_'.@(int)$plan->Ref_BoPhan;
                $khuvuc = (int) $plan->Ref_KhuVuc;
                $thietbi = (int) $plan->Ref_MaThietBi;
                $bophan = (int) $plan->Ref_BoPhan;
                $chuky = (int) $plan->ChuKyIOID;

                $this->_addPlan($plan, $tStartDate);
                
                if($plan->CanCu == 1 || $plan->CanCu == 2)
                {
                    $arrChiSo[$khuvuc][$thietbi][$bophan][$chuky] = 1;
                }
            }
            $tStartDate = Qss_Lib_Date::add_date($tStartDate, 1);
        }
    }

    public function cmp($a, $b)
    {
        return strtotime($a->StartDate) - strtotime($b->StartDate);
    }

    private function _sortPlans($startDate, $endDate)
    {
        usort($this->plans, array($this, 'cmp'));

        $this->calendar = array();
        $solar = new Qss_Model_Calendar_Solar();
        $arrOrder = $solar->createDateRangeArray($startDate, $endDate);
        $arrOrder = array_flip($arrOrder);
        foreach ($this->plans as $key=>$item)
        {
            $item->Ref_DVBT = (int)$item->Ref_DVBT;
            $start  = $item->StartDate;
            $end    = $item->EndDate;
            $amount = $item->Amount;
            $y      = 1;

            if(Qss_Lib_Date::compareTwoDate($item->StartDate, $startDate) == -1)
            {
                // Ngay bat dau bang ngay dau tien
                $start = $startDate;
            }

            if(Qss_Lib_Date::compareTwoDate($item->EndDate, $endDate) == 1)
            {
                $end    = $endDate;
                $amount = Qss_Lib_Date::diffTime($start, $endDate, 'D');
            }
            $startcol = $arrOrder[$start]+1;
            $endcol = $arrOrder[$end]+1;
            $i = 1;
            while(1)
            {
                $ok = true;
                for($j=$startcol;$j<=$endcol;$j++)
                {
                    if(isset($this->calendar[$item->Ref_DVBT][$i][$j]))
                    {
                        $ok = false;
                        break;
                    }

                }

                if($ok)
                {
                    for($j=$startcol;$j<=$endcol;$j++)
                    {
                        $add = new stdClass();

                        if($j == $startcol)
                        {
                            $add           = $item;
                            $add->colstart = true;
                            $add->colspan  = $amount;
                        }
                        else
                        {
                            $add->colstart = false;
                            $add->colspan  = 0;
                        }
                        $this->calendar[$item->Ref_DVBT][$i][$j] = $add;
                        $this->arrDVBT[$item->Ref_DVBT] = $item->DVBT;
                    }
                    break;
                }
                $i++;
            }
        }

        // echo '<pre>'; print_r($this->calendar); die;
    }
}
?>