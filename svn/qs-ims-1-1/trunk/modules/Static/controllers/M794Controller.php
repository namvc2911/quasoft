<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M794Controller extends Qss_Lib_Controller
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

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Breakdown();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	
	public function indexAction()
	{
        $mCommon     = new Qss_Model_Extra_Extra();
        $eqs         = $mCommon->getTable(array('*'), 'ODanhSachThietBi', array(), array('MaThietBi'), 100000);
        $eqsDialbox  = array();
        $eqIndex     =   0;

        foreach($eqs as $eq)
        {
            $eqsDialbox[0]['Dat'][$eqIndex]['ID']      = $eq->IOID;
            $eqsDialbox[0]['Dat'][$eqIndex]['Display'] = "{$eq->MaThietBi} - {$eq->TenThietBi}";
            $eqIndex++;
        }

        $this->html->eqsDialbox = $eqsDialbox;
	}

	public function showAction()
	{
		$eqLib        = new Qss_Lib_Maintenance_Equipment();
		$solar        = new Qss_Model_Calendar_Solar();
		$common       = new Qss_Model_Extra_Extra();
		$workOrder    = new Qss_Model_Maintenance_Workorder();
		$eqIOID       = $this->params->requests->getParam('equips' , array(0));
		$eqs          = $common->getTableFetchAll('ODanhSachThietBi', sprintf(' IOID IN (%1$s) ', implode(',', $eqIOID)));

        $minStart     = '';
        $depreciation = array();
        $deVal        = 0;
        $woInfo       = array();
        $cost         = array();
        $depreciation['Diff'] = 0;
        $depreciation['Lost'] = 0;
        $depreciation['Book'] = 0;
        $nguyenGia = 0;
        $thanhLy   = 0;

        foreach($eqs as $eq)
        {
            $method       = @(int)$eq->TinhKhauHao?$eq->TinhKhauHao:1;
            $temp         = array();

            switch ($method)
            {
                case Qss_Lib_Extra_Const::DEPRECIATION_STRAIGHT_LINE:
                    $temp = $this->getDepreciationByStraightLine($eq);
                break;

                case Qss_Lib_Extra_Const::DEPRECIATION_UNITS_OF_ACTIVITY:
                    $temp = $this->getDepreciationByUnitOfActivity($eq);
                break;

                case Qss_Lib_Extra_Const::DEPRECIATION_DECLINING_BALANCE:
                    $temp = $this->getDepreciationByDecliningBalance($eq);
                break;
            }

            if(count($temp))
            {
                $depreciation['Diff'] += $temp['Diff'];
                $depreciation['Lost'] += $temp['Lost'];
                $depreciation['Book'] += $temp['Book'];
            }


            if($eq->NgayDuaVaoSuDung)
            {
                if($minStart == '' || Qss_Lib_Date::compareTwoDate($minStart, $eq->NgayDuaVaoSuDung) == 1)
                {
                    $minStart = $eq->NgayDuaVaoSuDung;
                }
            }

            $deVal += $eqLib->getDepreciationValue($eq->IOID);

            $woInfo[$eq->IOID] = $workOrder->countWorkOrderByEquip($eq->IOID);

            $cost     = $workOrder->getCostByPeriod('Y' , $eq->NgayDuaVaoSuDung, date('Y-m-d'), 0, 0, $eq->IOID);

            foreach($cost as $val)
            {
                if(!isset($cost[(int)$val->Nam]))
                {
                    $cost[(int)$val->Nam] = 0;
                }
                $cost[(int)$val->Nam] += (int)$val->ThanhTien;
            }

            $nguyenGia+= $eq->NguyenGia?$eq->NguyenGia:0;
            $thanhLy  += $eq->GiaTriThanhLy?$eq->GiaTriThanhLy:0;
        }

        $rWoInfo = new stdClass();
        $rWoInfo->NumOfMaintain = 0;
        $rWoInfo->NumOfBreak    = 0;

        foreach($woInfo as $item)
        {
            $rWoInfo->NumOfMaintain += $item->NumOfMaintain;
            $rWoInfo->NumOfBreak    += $item->NumOfBreak;
        }



		$this->html->eq     = count($eqs) == 1?$eqs[0]:false;
		$this->html->de     = $depreciation;
		$this->html->deVal  = $deVal;
		$this->html->years  = $minStart?$solar->createYearRangeArray($minStart, date('Y-m-d')):array();
        $this->html->months = $minStart?Qss_Lib_Date::divDate($minStart, date('Y-m-d'), 'MO'):1;
        $this->html->nYears = $minStart?Qss_Lib_Date::divDate($minStart, date('Y-m-d'), 'Y'):1;
		$this->html->woInfo = $rWoInfo;
        $this->html->cost   = $cost;
        $this->html->thanhLy   = $thanhLy;
        $this->html->nguyenGia = $nguyenGia;

	}
	 /**
     * Tính khấu hao bằng phương pháp đường thẳng (hay thời gian)
     * - giá trị khấu hao = nguyên giá - giá trị thanh lý
     * - %khấu hao theo năm = 100% chia cho số năm hoạt động
     * - khấu hao theo năm = %khấu hao theo năm * giá trị khấu hao
     * - giá trị mất đi = giá trị mất đi năm trước + khấu hao theo năm 
     * - giá trị còn lại = nguyên giá - giá trị mất đi
     * @param object $eq object thiết bị
     */
    private function getDepreciationByStraightLine($eq) {
        $aRetval             = array();
        // So nam hoat dong thuc te tinh theo ngay chia ra thanh nam
        $iActYearsBaseOnDate = Qss_Lib_Extra::countActiveYearsBaseOnDate($eq->NgayDuaVaoSuDung); 
        // So nam hoat dong thuc te tinh theo nam
        $iActYearsBaseOnYear = Qss_Lib_Extra::countActiveYearsBaseOnYear($eq->NgayDuaVaoSuDung); 
        // So nam khau hao
        $fDeYears            = $eq->GiaTri;
        // Lam tron so nam khau hao
        $iDeYears            = ceil($eq->GiaTri);

        
        if($iActYearsBaseOnDate && $fDeYears) 
        {
            // Nam dau tien hoat dong
            $iStartYear     = (int)date('Y', strtotime($eq->NgayDuaVaoSuDung));
            // Nam khau hao ve khong
            $iEndYear       = (int)date('Y', strtotime($eq->NgayDuaVaoSuDung.' + '.$iDeYears.' year'));
            // Ngay khau hao cuoi cung
            $sDeEndDate     = date('Y-m-d', strtotime($eq->NgayDuaVaoSuDung.' + '.ceil($fDeYears * 365).' days'));
            // "Ngay trong nam" cua ngay dua vao su dung (bat dau tu 0)
            $iBeginDay      = (int)date('z', strtotime($eq->NgayDuaVaoSuDung));
            // "Ngay trong nam" cua ngay hien tai (bat dau tu 1)
            $iEndDay        = (int)date('z', strtotime($sDeEndDate)) + 1;
            // Gia tri tinh khau hao
            $fDeValue       = $eq->NguyenGia - $eq->GiaTriThanhLy;
            // Phan tram khau hao hang nam
            $fDePercent     = 100/$fDeYears;
            // Gia tri khau hao hang nam
            $fAnnualDeValue = ($fDeValue * $fDePercent)/100;
            // Gia tri con lai 
            $fBook          = $fDeValue;
            
            // Tinh khau hao hang nam
            for($i = 1; $i <= $iActYearsBaseOnYear; $i++)
            {
                // Neu gia tri khau hao da het khong tiep tuc lap nua
                if($fBook <= 0)
                {
                    break;
                }
                
                // reset lai gia tri khau hao
                $fTempAnnualDeValue = $fAnnualDeValue;
                
                // Vao nam dau gia tri khau hao phai tinh tu ngay bat dau sd
                if($i == 1)
                {
                    $fTempAnnualDeValue *= ((365 - $iBeginDay)/365);
                }
                // Vao nam cuoi gt khau hao phai tinh tu dau nam den ngay hien tai
                //(chi tinh voi thiet bi co it nhat 2 nam hd)
                elseif($iStartYear == $iEndYear && $i > 1)
                {
                    $fTempAnnualDeValue *= $iEndDay/365;
                }
                
                // Lay nam truoc do
                $iPrevYear       = $iStartYear - 1;
                // Gia tri mat cua nam truoc do
                $prevYearLostVal = isset($retval[$iPrevYear]['Lost'])?$retval[$iPrevYear]['Lost']:0;
                // Gia tri con lai sau khi tru khau hao
                $fBook           = $fBook - $fTempAnnualDeValue;
                
                // Khoan khau hao hang nam
                $aRetval[$iStartYear]['Diff'] = $fTempAnnualDeValue;
                // Khoang mat di cong don nam truoc
                $aRetval[$iStartYear]['Lost'] = $prevYearLostVal + $fTempAnnualDeValue;
                // Khoan gia tri khau hao con lai
                $aRetval[$iStartYear]['Book'] = $fBook;  
                
                
                // Voi truong hop khau hao du ngay (ngay hien tai vuot qua ngay cuoi khau hao)
                // Gia tri khau hao con lai bi am can dieu chinh lai de gia tri khau hao ve 0
                if($fBook < 0)
                {
                    $aRetval[$iStartYear]['Diff'] += $fBook;
                    $aRetval[$iStartYear]['Lost'] += $fBook;
                    $aRetval[$iStartYear]['Book'] = 0;                      
                }
                
                $iStartYear++;
            }
        }
        return $aRetval;
    }
    
    /**
     * Tính khấu hao bằng phương pháp dựa trên chỉ số hoạt động
     * giá trị khấu hao = nguyên giá - giá trị thanh lý
     * 1 đơn vị hoạt động = giá trị khấu hao chia cho tổng số hoạt động
     * khấu hao theo năm = 1 đơn vị hoạt động * tổng số đơn vị hoạt động của năm
     * - giá trị mất đi = giá trị mất đi năm trước + khấu hao theo năm 
     * - giá trị còn lại = nguyên giá - giá trị mất đi
     * @param object $eq object thiết bị
     */    
    private function getDepreciationByUnitOfActivity($eq)
    {
        $aRetval         = array();
        $eqModel         = new Qss_Model_Maintenance_Equipment();
        // So don vi khau hao het gia tri
        $fDeMaxUnit      = $eq->GiaTri;      
        // Chi so hoat dong
        $iDeParam        = $eq->Ref_ChiSo;
        
        if($fDeMaxUnit && $iDeParam)
        {
            // Gia tri tinh khau hao
            $fDeValue        = $eq->NguyenGia - $eq->GiaTriThanhLy;            
            // 1 Don vi khau hao het
            $fDeUnitValue    = $fDeValue/$fDeMaxUnit;
            // Gia tri con lai 
            $fBook          = $fDeValue;            
            // nhat trinh cua thiet bi tinh tu ngay dua vao sd den ngay hien tai
            // Tong hoat dong theo nam
            $oSumParamByYear = $eqModel->getDailyRecordFromStartDateOfEquipGroupByYear($eq->IOID, @(int)$eq->Ref_ChiSo, $eq->NgayDuaVaoSuDung);
            $iStartYear     = (int)date('Y', strtotime($eq->NgayDuaVaoSuDung));
            
            
            // Tinh khau hao theo chi so
            foreach($oSumParamByYear as $param)
            {
                // Neu gia tri khau hao da het khong tiep tuc lap nua
                if($fBook <= 0)
                {
                    break;
                }                
                
                // Khoan khau hao theo chi so hoat dong theo nam
                $fDiff  = $param->Total * $fDeUnitValue;
                // Lay nam truoc do
                $iPrevYear       = (int)$param->Year - 1;
                // Gia tri mat cua nam truoc do
                $prevYearLostVal = isset($retval[$iPrevYear]['Lost'])?$retval[$iPrevYear]['Lost']:0;
                // Gia tri con lai sau khi tru khau hao
                $fBook           = $fBook - $fDiff;                
                
                // Khoan khau hao hang nam
                $aRetval[(int)$param->Year]['Diff'] = $fDiff;
                // Khoang mat di cong don nam truoc
                $aRetval[(int)$param->Year]['Lost'] = $prevYearLostVal + $fDiff;
                // Khoan gia tri khau hao con lai
                $aRetval[(int)$param->Year]['Book'] = $fBook;      
                
                // Neu qua so luong chi so max, thi so con lai se ve 0
                if($fBook < 0)
                {
                    $aRetval[$iStartYear]['Diff'] += $fBook;
                    $aRetval[$iStartYear]['Lost'] += $fBook;
                    $aRetval[$iStartYear]['Book'] = 0;                      
                }

                $iStartYear++;
            }
        }
        
        return $aRetval;
    }       
    
    /**
     * Tính khấu hao bằng phương pháp phần trăm số dư giảm dần
     * - khau hao theo nam = nguyen gia * phan tram khau hao theo nam
     * - giá trị mất đi = giá trị mất đi năm trước + khấu hao theo năm 
     * - giá trị còn lại = nguyên giá - giá trị mất đi
     * @param object $eq object thiết bị
     */    
    private function getDepreciationByDecliningBalance($eq)
    {
        $aRetval             = array();
        // So nam hoat dong thuc te tinh theo ngay chia ra thanh nam
        $iActYearsBaseOnDate = Qss_Lib_Extra::countActiveYearsBaseOnDate($eq->NgayDuaVaoSuDung); 
        // So nam hoat dong thuc te tinh theo nam
        $iActYearsBaseOnYear = Qss_Lib_Extra::countActiveYearsBaseOnYear($eq->NgayDuaVaoSuDung); 
        // So nam khau hao
        $fDePercent          = $eq->GiaTri;

        
        if($iActYearsBaseOnDate && $fDePercent) 
        {
            // Nam dau tien hoat dong
            $iStartYear     = (int)date('Y', strtotime($eq->NgayDuaVaoSuDung));
            // Nam khau hao ve khong
            $iEndYear       = (int)date('Y');
            // Ngay khau hao cuoi cung
            $sDeEndDate     = date('Y-m-d');
            // "Ngay trong nam" cua ngay dua vao su dung (bat dau tu 0)
            $iBeginDay      = (int)date('z', strtotime($eq->NgayDuaVaoSuDung));
            // "Ngay trong nam" cua ngay hien tai (bat dau tu 1)
            $iEndDay        = (int)date('z', strtotime($sDeEndDate)) + 1;
            // Gia tri tinh khau hao
            $fDeValue       = $eq->NguyenGia ;//- $eq->GiaTriThanhLy;
            // Gia tri con lai 
            $fBook          = $fDeValue;            
            
            // Tinh khau hao hang nam
            for($i = 1; $i <= $iActYearsBaseOnYear; $i++)
            {
                // Neu gia tri khau hao da het khong tiep tuc lap nua
                if($fBook <= $eq->GiaTriThanhLy)
                {
                    break;
                }
                
                // reset lai gia tri khau hao
                $fTempAnnualDeValue = ($fBook * $fDePercent)/100;
                
                // Vao nam dau gia tri khau hao phai tinh tu ngay bat dau sd
                if($i == 1)
                {
                    $fTempAnnualDeValue *= ((365 - $iBeginDay)/365);
                }
                // Vao nam cuoi gt khau hao phai tinh tu dau nam den ngay hien tai
                //(chi tinh voi thiet bi co it nhat 2 nam hd)
                elseif($iStartYear == $iEndYear && $i > 1)
                {
                    $fTempAnnualDeValue *= $iEndDay/365;
                }
                
                // Lay nam truoc do
                $iPrevYear       = $iStartYear - 1;
                // Gia tri mat cua nam truoc do
                $prevYearLostVal = isset($retval[$iPrevYear]['Lost'])?$retval[$iPrevYear]['Lost']:0;
                // Gia tri con lai sau khi tru khau hao
                $fBook           = $fBook - $fTempAnnualDeValue;
                
                // Khoan khau hao hang nam
                $aRetval[$iStartYear]['Diff'] = $fTempAnnualDeValue;
                // Khoang mat di cong don nam truoc
                $aRetval[$iStartYear]['Lost'] = $prevYearLostVal + $fTempAnnualDeValue;
                // Khoan gia tri khau hao con lai
                $aRetval[$iStartYear]['Book'] = $fBook;  
                
                
                // Voi truong hop khau hao du ngay (ngay hien tai vuot qua ngay cuoi khau hao)
                // Gia tri khau hao con lai bi am can dieu chinh lai de gia tri khau hao ve 0
                if($fBook < $eq->GiaTriThanhLy)
                {
                    $aRetval[$iStartYear]['Diff'] += ($eq->GiaTriThanhLy - $fBook);
                    $aRetval[$iStartYear]['Lost'] += ($eq->GiaTriThanhLy - $fBook);
                    $aRetval[$iStartYear]['Book'] = $eq->GiaTriThanhLy;                      
                }
                
                $iStartYear++;
            }
        }
        return $aRetval;        
    } 
}
?>