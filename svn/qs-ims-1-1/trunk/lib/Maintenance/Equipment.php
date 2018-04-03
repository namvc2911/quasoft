<?php

class Qss_Lib_Maintenance_Equipment
{
	public function __construct()
	{

	}
    
    /**
     * ________________________________________________________________________
     * 
     * THONG TIN CO BAN VE THIET BI - INFO
     * ________________________________________________________________________
     */    
    
    /**
     * Lay chi tiet don vi quan ly thiet bi
     * @param type $eqIOID
     * @return type
     */
    public function getManageDepOfEquip($eqIOID)
    {
        $locModel = new Qss_Model_Maintenance_Location();
        $deps     = $locModel->getManageDepOfEquip($eqIOID);
        
        return $deps;
    }
    
    /**
     * Lay ten don vi quan ly thiet bi
     * @param type $eqIOID
     * @return type
     */
    public function getManageDepNameOfEquip($eqIOID)
    {
        $deps        = $this->getManageDepOfEquip($eqIOID);
        $workcenters = array();
        
        foreach($deps as $wc)
        {
            if($wc->IOID &&  !in_array($wc->Ten, $workcenters))
            $workcenters[] = $wc->Ten;
        }
        
        return implode(', ', $workcenters);
    }    
    

    
    /**
     * ________________________________________________________________________
     * 
     * CHI PHI BAO TRI - COST
     * ________________________________________________________________________
     */
    
    /**
     * Lay chi phi bao tri hang nam cua thiet bi
     * @param type $eqIOID
     * @param type $startDate
     * @return type
     */    
    public function getAnnualMaintainCostOfEquip($eqIOID, $startDate)
    {
        $maintain = new Qss_Model_Maintenance_Equipment();
        $cost     = $maintain->getCostByPeriod('Y' , $startDate, date('Y-m-d'), 0, 0, $eqIOID);
        $retval   = array();
        
        foreach($cost as $val)
        {
            $retval[(int)$val->Nam] = (int)$val->ThanhTien;
        }
        return $retval;
    }
    
    /**
     * ________________________________________________________________________
     * 
     * KHAU HAO THIET BI - DEPRECIATION
     * ________________________________________________________________________
     */    
    
    /**
     * Lấy dữ liệu cho báo cáo khấu hao thiết bị
     * @param int $eqIOID IOID của thiết bị
     */
    public function getEquipDepreciationData($eqIOID) {
        $common       = new Qss_Model_Extra_Extra();
        $eq           = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$eqIOID));
        $method       = @(int)$eq->TinhKhauHao?$eq->TinhKhauHao:1;
        $depreciation = array();
        
        switch ($method) {
            case Qss_Lib_Extra_Const::DEPRECIATION_STRAIGHT_LINE: 
                $depreciation = $this->getDepreciationByStraightLine($eq);
            break;
        
            case Qss_Lib_Extra_Const::DEPRECIATION_UNITS_OF_ACTIVITY: 
                $depreciation = $this->getDepreciationByUnitOfActivity($eq);
            break;   
        
            case Qss_Lib_Extra_Const::DEPRECIATION_DECLINING_BALANCE:
                $depreciation = $this->getDepreciationByDecliningBalance($eq);
            break;         
        }
        
        return $depreciation;
    }
    
    /**
     * Lay gia tri con lai cua thiet bi
     * @param type $eqIOID
     * @return int money
     */
    public function getBookValue($eqIOID)
    {
        $dep  = $this->getEquipDepreciationData($eqIOID);
        $last = array();
        
        if(count((array)$dep))
        {
            $last = end($dep);
        }
        return count($last)?$last['Book']:0;
    }
    
    public function getDepreciationValue($eqIOID) {
        $common       = new Qss_Model_Extra_Extra();
        $eq           = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$eqIOID));
        $method       = @(int)$eq->TinhKhauHao?$eq->TinhKhauHao:1;
        $depreciation = 0;        
        
        switch ($method) {
            case Qss_Lib_Extra_Const::DEPRECIATION_STRAIGHT_LINE: 
                $depreciation = $eq->NguyenGia - $eq->GiaTriThanhLy;
            break;
        
            case Qss_Lib_Extra_Const::DEPRECIATION_UNITS_OF_ACTIVITY: 
                $depreciation = $eq->NguyenGia - $eq->GiaTriThanhLy;
            break;   
        
            case Qss_Lib_Extra_Const::DEPRECIATION_DECLINING_BALANCE:
                $depreciation = $eq->NguyenGia ;
            break;         
        }
        return $depreciation;
    }
    
    /**
     * Lấy thông tin bảo trì cho thiết bị tính khấu hao
     * Bao gồm: số lần bảo trì, số lần sự cố, sự cố trung bình tháng, vật tư thay thế
     * @param int $eqIOID IOID của thiết bị
     */
    private function getMaintainInfoOfEquip($eqIOID)
    {
        
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
