<?php
/**
 * Class Static_M158Controller
 * Kết quả tiêu thụ điện năng hàng tháng
 */
class Static_M158Controller extends Qss_Lib_Controller
{  
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();         
    }
    
    public function indexAction()
    {
        
    }
    
    public function showAction()
    {
        $month     = $this->params->requests->getParam('month', 0);
        $year      = $this->params->requests->getParam('year', 0);

        $this->html->month  = @(int)$month;
        $this->html->year   = @(int)$year;
        $this->html->report = $this->_getReportData($month, $year);
    }  

    
    public function _getReportData($month, $year)
    {
        $manufacturing = new Qss_Model_Extra_Manufacturing();
        $electric      = new Qss_Model_Maintenance_Electric();
        $arrDienNang   = array();
        $data          = $manufacturing->getManufacturingByMonth(@(int)$month, @(int)$year);
        
        $lastMonth     = @(int)$month;
        $yearOfLast    = @(int)$year;
        $monthDiffOne  = 0;
        
        if($month == 1)
        {
            $lastMonth  = 12;
            $yearOfLast = $yearOfLast - 1;
        }
        else
        {
            $lastMonth = $lastMonth - 1;
        }
        
        // Dien nang thang truoc
        $electricLibAgo = Qss_Lib_Maintenance_Electric_Calculate::createInstance($lastMonth, $yearOfLast);
        $electricLibAgo->calculateDienMuaTuQuangNinh();
        $electricLibAgo->calculateDienBanChoQuangNinh();
        $electricLibAgo->calculateDienNangMuaVao();
        $electricLibAgo->calculateDienNangBanRa();
        $electricLibAgo->calculateChechLechMuaBan();
        $electricLibAgo->calculateDienNangMuaVaoTheoKy();
        $electricLibAgo->calculateTongDienMuaVaoTheoDonVi();
        $electricLibAgo->calculateTongDienBanRaTheoDonVi();
        $electricLibAgo->calculateKhoanDienNangChoDonVi();
        $electricLibAgo->calculateTongSanLuongDonVi();
        $electricLibAgo->calculateSanLuongDonVi();
        $electricLibAgo->calculateHeSoTonHao();
        $electricLibAgo->calculateSanLuongNghiemThuDonVi();        
        
        // Dien nang thang nay
        $electricLib = Qss_Lib_Maintenance_Electric_Calculate::createInstance($month, $year);
        $electricLib->calculateDienMuaTuQuangNinh();
        $electricLib->calculateDienBanChoQuangNinh();
        $electricLib->calculateDienNangMuaVao();
        $electricLib->calculateDienNangBanRa();
        $electricLib->calculateChechLechMuaBan();
        $electricLib->calculateDienNangMuaVaoTheoKy();
        $electricLib->calculateTongDienMuaVaoTheoDonVi();
        $electricLib->calculateTongDienBanRaTheoDonVi();
        $electricLib->calculateKhoanDienNangChoDonVi();
        $electricLib->calculateTongSanLuongDonVi();
        $electricLib->calculateSanLuongDonVi();
        $electricLib->calculateHeSoTonHao();
        $electricLib->calculateSanLuongNghiemThuDonVi();
        
        // gan dien nang thang truoc 
        foreach($electricLibAgo->getSanLuongNghiemThuDonVi() as $deptID=>$val)
        {
            $arrDienNang[$deptID]['OneMonthAgoElectric'] = $val;
        }
        
        // gan dien nang thang nay
        foreach($electricLib->getSanLuongNghiemThuDonVi() as $deptID=>$val)
        {
            $arrDienNang[$deptID]['Electric'] = $val;
        }        
        
        //echo '<pre>'; print_r($data); die;
    
        // Tinh san luong cach mot thang
        foreach($data as $om)
        {
            $dienNangThangTruoc    = isset($arrDienNang[$om->DepartmentID]['OneMonthAgoElectric'])?$arrDienNang[$om->DepartmentID]['OneMonthAgoElectric']:0;
            $dienNang              = isset($arrDienNang[$om->DepartmentID]['Electric'])?$arrDienNang[$om->DepartmentID]['Electric']:0;
            
            //echo '<pre>'; print_r(Qss_Lib_Util::formatInteger($dienNang)); die;
            
            $suatTieuHaoThangTruoc = $om->ThanSachThangTruoc?$dienNangThangTruoc/$om->ThanSachThangTruoc:0;        
            $suatTieuHaoThangNay   = $om->ThanSach?$dienNang/$om->ThanSach:0;
            $suatTieuHaoDonVi      = $om->KeHoachSanLuongDonVi?$om->KeHoachDienNangDonVi/$om->KeHoachSanLuongDonVi:0;
            
            $diffWithCurrent       = $suatTieuHaoThangNay - $suatTieuHaoDonVi;
            $diffWithOneMonthAgo   = $suatTieuHaoThangTruoc - $suatTieuHaoDonVi;
            
            $diffWithCurrentPercent     = $suatTieuHaoDonVi?($suatTieuHaoThangNay/$suatTieuHaoDonVi)*100:0;
            $diffWithOneMonthAgoPercent = $suatTieuHaoDonVi?($suatTieuHaoThangTruoc/$suatTieuHaoDonVi)*100:0;            
            
            
            
            $om->OneMonthAgoAmount          = Qss_Lib_Util::formatInteger($om->ThanSachThangTruoc);
            $om->OneMonthAgoElectric        = Qss_Lib_Util::formatInteger($dienNangThangTruoc);
            $om->OneMonthAgoLoss            = Qss_Lib_Util::formatNumber($suatTieuHaoThangTruoc);
            
            $om->PlanAmount                  = Qss_Lib_Util::formatInteger($om->KeHoachSanLuongDonVi);
            $om->PlanElectric              = Qss_Lib_Util::formatInteger($om->KeHoachDienNangDonVi);
            $om->PlanLoss            = Qss_Lib_Util::formatNumber($suatTieuHaoDonVi);    
            
            $om->Amount              = Qss_Lib_Util::formatInteger($om->ThanSach);
            $om->Electric            = Qss_Lib_Util::formatInteger($dienNang);
            $om->Loss          = Qss_Lib_Util::formatNumber($suatTieuHaoThangNay);            

            $om->DiffWithCurrent            = Qss_Lib_Util::formatNumber($diffWithCurrent);
            $om->DiffWithCurrentPercent     = Qss_Lib_Util::formatNumber($diffWithCurrentPercent);
            $om->DiffWithOneMonthAgo        = Qss_Lib_Util::formatNumber($diffWithOneMonthAgo);
            $om->DiffWithOneMonthAgoPercent = Qss_Lib_Util::formatNumber($diffWithOneMonthAgoPercent);
            
//             $retval[$om->DepartmentID]['Name']                = $om->DeptName;
//             $retval[$om->DepartmentID]['Code']                = $om->DeptCode;
            
//             $retval[$om->DepartmentID]['OneMonthAgoAmount']   = Qss_Lib_Util::formatInteger($om->ThanSachThangTruoc);
//             $retval[$om->DepartmentID]['OneMonthAgoElectric'] = Qss_Lib_Util::formatInteger($dienNangThangTruoc);
//             $retval[$om->DepartmentID]['OneMonthAgoLoss']     = Qss_Lib_Util::formatNumber($suatTieuHaoThangTruoc);
            
//             $retval[$om->DepartmentID]['Amount']              = Qss_Lib_Util::formatInteger($om->ThanSach);
//             $retval[$om->DepartmentID]['Electric']            = Qss_Lib_Util::formatInteger($dienNang);
//             $retval[$om->DepartmentID]['Loss']                = Qss_Lib_Util::formatNumber($suatTieuHaoThangNay);

//             $retval[$om->DepartmentID]['PlanAmount']          = Qss_Lib_Util::formatInteger($om->KeHoachSanLuongDonVi);
//             $retval[$om->DepartmentID]['PlanElectric']        = Qss_Lib_Util::formatInteger($om->KeHoachDienNangDonVi);
//             $retval[$om->DepartmentID]['PlanLoss']            = Qss_Lib_Util::formatNumber($suatTieuHaoDonVi);        

//             $retval[$om->DepartmentID]['DiffWithCurrent']            = Qss_Lib_Util::formatNumber($diffWithCurrent);
//             $retval[$om->DepartmentID]['DiffWithCurrentPercent']     = Qss_Lib_Util::formatNumber($diffWithCurrentPercent);
//             $retval[$om->DepartmentID]['DiffWithOneMonthAgo']        = Qss_Lib_Util::formatNumber($diffWithOneMonthAgo);
//             $retval[$om->DepartmentID]['DiffWithOneMonthAgoPercent'] = Qss_Lib_Util::formatNumber($diffWithOneMonthAgoPercent);            
        }
    
        //echo '<pre>'; print_r($data); die;
        return $data;
    
    }
    
    
}