<?php
/**
 * Class Static_M154Controller
 * Báo cáo theo dõi mua điện năng hàng tháng
 */
class Static_M154Controller extends Qss_Lib_Controller
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
        $start = $this->params->requests->getParam('start', 0);
        $end   = $this->params->requests->getParam('end', 0);
        $year  = $this->params->requests->getParam('year', 0);
        
        if($end == 0)
        {
            $end = $start;
        }
        
        $this->html->start = @(int)$start;
        $this->html->end   = @(int)$end;
        $this->html->year  = @(int)$year;        
        $this->html->report = $this->_getReportData(@(int)$start, @(int)$end, @(int)$year);
    }
    
    public function _getReportData($start, $end, $year)
    {
        $electric  = new Qss_Model_Maintenance_Electric();
        $ret       = new stdClass();
        $ret2      = new stdClass();
        $i         = -1;
        
        for($iMonth = $start; $iMonth <= $end; $iMonth++)
        {
            $data = $electric->getChiSoDienNangMuaVao($iMonth, $year);
            
            foreach($data as $dat)
            {
                $code = "{$dat->CongToIOID}-{$dat->Ky}";
                
                if(!isset($ret->{$code}))
                {
                    $ret->{$code} = $dat;
                }
                else 
                {
                    $ret->{$code}->HeSo = $dat->HeSo;
                    $ret->{$code}->ChiSoCuoi = $dat->ChiSoCuoi;
                    $ret->{$code}->TongSo += $dat->TongSo;
                    $ret->{$code}->ChiSoCuoiThapDiem = $dat->ChiSoCuoiThapDiem;
                    $ret->{$code}->TongSoThapDiem += $dat->TongSoThapDiem;
                    $ret->{$code}->ChiSoCuoiTrungBinh = $dat->ChiSoCuoiTrungBinh;
                    $ret->{$code}->TongSoTrungBinh += $dat->TongSoTrungBinh;
                    $ret->{$code}->ChiSoCuoiCaoDiem = $dat->ChiSoCuoiCaoDiem;
                    $ret->{$code}->TongSoCaoDiem += $dat->TongSoCaoDiem;
                    $ret->{$code}->TongSoCoTonHao += $dat->TongSoCoTonHao;
                    $ret->{$code}->TongSoCaoDiemCoTonHao += $dat->TongSoCaoDiemCoTonHao;
                    $ret->{$code}->TongSoThapDiemCoTonHao += $dat->TongSoThapDiemCoTonHao;
                    $ret->{$code}->TongSoTrungBinhCoTonHao += $dat->TongSoTrungBinhCoTonHao;
                    $ret->{$code}->TongTienPhatCosPi += $dat->TongTienPhatCosPi;
                } 
            }
        }
        
        foreach($ret as $r1)
        {
            $ret2->{++$i} = $r1;
        }
        
        return $ret2;
    }
    
    
}