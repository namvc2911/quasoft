<?php
/**
 * Class Static_M815Controller
 * Báo cáo tiêu thụ điện năng hàng tháng
 */
class Static_M815Controller extends Qss_Lib_Controller
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
        $retval      = array();
        $oldDongMua  = '';
        $oldDongBan  = '';
        $numMonth    = ($end - $start) + 1;
        
        $retval['SoDong']               = 0;
        $retval['TongDienMuaDienLuc']   = 0;
        $retval['TongTienMuaDienLuc']   = 0;
        $retval['CospiBanDienLuc']      = 0;
        $retval['TongDienBanDienLuc']   = 0;
        $retval['TongTienBanDienLuc']   = 0;
        $retval['TongDienMua']          = 0;
        $retval['TongTienMua']          = 0;
        $retval['TongDienBan']          = 0;
        $retval['TongTienBan']          = 0;
        $retval['ChenhLechMuaBan']      = 0;
        $retval['TongTienBanCuoi']      = 0;
        $retval['DienConLaiSuDungCuoi'] = 0;
        $retval['TienConLaiSuDungCuoi'] = 0;
        $retval['SoThang']              = $numMonth;
        
        for($iMonth = $start; $iMonth <= $end; $iMonth++)
        {
            $sodongmua   = 5;
            $sodongban   = 3;
            
            $electricLib = Qss_Lib_Maintenance_Electric_Calculate::createInstance($iMonth, $year);//new Qss_Lib_Maintenance_Electric_Calculate($month, $year);
            $electricLib->setGiaMua();
            $electricLib->setGiaBan();
            $electricLib->calculateDienMuaTuQuangNinh();
            $electricLib->calculateDienBanChoQuangNinh();
            $electricLib->calculateDienNangMuaVao();
            $electricLib->calculateDienNangBanRa();
            $electricLib->calculateChechLechMuaBan();
            
            
            foreach($electricLib->datasetMuaVao as $item)
            {
                // Don gia
                $CongToIOID       = @(int)$item->CongToIOID;
                $giaDienChung     = ($electricLib->getGiaMuaChung($CongToIOID));
                $giaDienB1        = ($electricLib->getGiaMuaB1($CongToIOID));
                $giaDienB2        = ($electricLib->getGiaMuaB2($CongToIOID));
                $giaDienB3        = ($electricLib->getGiaMuaB3($CongToIOID));

                 
                // Thanh tien
                $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
                $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
                $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
                $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
                $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
                 
                // Tong ba so dien cong to ba gia
                $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
            
                $sodongmua       += ($item->LoaiGia == 2 && $item->Tram35)?4:0; // loai 1 gia + 1 dong, loai 3 gia cong 4 dong
            
                if(!isset($retval['Mua'][$item->DoiTuong][$item->CongToIOID]))
                {
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DoiTuong             = $item->DoiTuong;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->Tram35               = $item->Tram35;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->ViTri                = $item->ViTri;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->MaCongTo             = $item->MaCongTo;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TenCongTo            = $item->TenCongTo;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->LoaiGia              = $item->LoaiGia;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonViCungCap         = $item->DonViCungCap;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->RefDonViCungCap      = $item->Ref_DonViCungCap;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienChung      = $item->TongSoDienChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienThapDiem   = $item->TongSoDienThapDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienTrungBinh  = $item->TongSoDienTrungBinh;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienCaoDiem    = $item->TongSoDienCaoDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongTienPhatCosPi    = $item->TongTienPhatCosPi;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaChung          = $giaDienChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaThapDiem       = $giaDienB3;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaTrungBinh      = $giaDienB1;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaCaoDiem        = $giaDienB2;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaChung         = $tongGiaChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienThapDiem  = $tongGiaThapDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienTrungBinh = $tongGiaTrungBinh;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienCaoDiem   = $tongGiaCaoDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongBaGia            = $tongBaGia;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongBaSoDien         = $tongBaSoDien;                    
                }
                else 
                {
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienChung      += $item->TongSoDienChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienThapDiem   += $item->TongSoDienThapDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienTrungBinh  += $item->TongSoDienTrungBinh;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongSoDienCaoDiem    += $item->TongSoDienCaoDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongTienPhatCosPi    += $item->TongTienPhatCosPi;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaChung          += $giaDienChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaThapDiem       += $giaDienB3;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaTrungBinh      += $giaDienB1;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->DonGiaCaoDiem        += $giaDienB2;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaChung         += $tongGiaChung;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienThapDiem  += $tongGiaThapDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienTrungBinh += $tongGiaTrungBinh;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongGiaDienCaoDiem   += $tongGiaCaoDiem;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongBaGia            += $tongBaGia;
                    $retval['Mua'][$item->DoiTuong][$item->CongToIOID]->TongBaSoDien         += $tongBaSoDien;                    
                }
            }
            
            if($oldDongMua === '' || $sodongmua > $oldDongMua)
            {
                $oldDongMua = $sodongmua;
            }
            
            foreach($electricLib->datasetBanRa as $item)
            {
                // Don gia
                $CongToIOID       = @(int)$item->CongToIOID;
                $giaDienChung     = ($electricLib->getGiaBanChung($CongToIOID));
                $giaDienB1        = ($electricLib->getGiaBanB1($CongToIOID));
                $giaDienB2        = ($electricLib->getGiaBanB2($CongToIOID));
                $giaDienB3        = ($electricLib->getGiaBanB3($CongToIOID));




                // Thanh tien
                $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
                $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
                $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
                $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
                $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
            
                // Tong ba so dien cong to ba gia
                $sodongban       += ($item->LoaiGia == 2)?4:1; // loai 1 gia + 1 dong, loai 3 gia cong 4 dong
                $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
            
                if(!isset($retval['Ban'][$item->CongToIOID]))
                {
                    $retval['Ban'][$item->CongToIOID]->DoiTuong             = $item->DoiTuong;
                    $retval['Ban'][$item->CongToIOID]->MaCongTo             = $item->Ma;
                    $retval['Ban'][$item->CongToIOID]->TenCongTo            = $item->Ten;
                    $retval['Ban'][$item->CongToIOID]->LoaiGia              = $item->LoaiGia;
                    $retval['Ban'][$item->CongToIOID]->DonViBan             = $item->DonViBan;
                    $retval['Ban'][$item->CongToIOID]->RefDonViMuaChinh     = $item->Ref_DonViMuaNgoai?$item->Ref_DonViMuaNgoai:0;
                    $retval['Ban'][$item->CongToIOID]->DonViMuaChinh        = ($item->DonViMuaNgoai?$item->DonViMuaNgoai:'UNKNOW');
                    $retval['Ban'][$item->CongToIOID]->TongTienPhatCosPi    = $item->TongTienPhatCosPi;
                    $retval['Ban'][$item->CongToIOID]->DonGiaChung          = $giaDienChung;
                    $retval['Ban'][$item->CongToIOID]->DonGiaThapDiem       = $giaDienB3;
                    $retval['Ban'][$item->CongToIOID]->DonGiaTrungBinh      = $giaDienB1;
                    $retval['Ban'][$item->CongToIOID]->DonGiaCaoDiem        = $giaDienB2;
                    $retval['Ban'][$item->CongToIOID]->TongGiaChung         = $tongGiaChung;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienThapDiem  = $tongGiaThapDiem;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienTrungBinh = $tongGiaTrungBinh;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienCaoDiem   = $tongGiaCaoDiem;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienChung      = $item->TongSoDienChung;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienThapDiem   = $item->TongSoDienThapDiem;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienTrungBinh  = $item->TongSoDienTrungBinh;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienCaoDiem    = $item->TongSoDienCaoDiem;
                    $retval['Ban'][$item->CongToIOID]->TongBaGia            = $tongBaGia;
                    $retval['Ban'][$item->CongToIOID]->TongBaSoDien         = $tongBaSoDien;                    
                }
                else 
                {
                    $retval['Ban'][$item->CongToIOID]->TongTienPhatCosPi    += $item->TongTienPhatCosPi;
                    $retval['Ban'][$item->CongToIOID]->DonGiaChung          += $giaDienChung;
                    $retval['Ban'][$item->CongToIOID]->DonGiaThapDiem       += $giaDienB3;
                    $retval['Ban'][$item->CongToIOID]->DonGiaTrungBinh      += $giaDienB1;
                    $retval['Ban'][$item->CongToIOID]->DonGiaCaoDiem        += $giaDienB2;
                    $retval['Ban'][$item->CongToIOID]->TongGiaChung         += $tongGiaChung;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienThapDiem  += $tongGiaThapDiem;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienTrungBinh += $tongGiaTrungBinh;
                    $retval['Ban'][$item->CongToIOID]->TongGiaDienCaoDiem   += $tongGiaCaoDiem;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienChung      += $item->TongSoDienChung;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienThapDiem   += $item->TongSoDienThapDiem;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienTrungBinh  += $item->TongSoDienTrungBinh;
                    $retval['Ban'][$item->CongToIOID]->TongSoDienCaoDiem    += $item->TongSoDienCaoDiem;
                    $retval['Ban'][$item->CongToIOID]->TongBaGia            += $tongBaGia;
                    $retval['Ban'][$item->CongToIOID]->TongBaSoDien         += $tongBaSoDien;                    
                }
            }
            
            foreach($electricLib->datasetBanDotXuat as $item)
            {
                // tang so dong ban
                $sodongban++;
                $code = 'DONVI'.$item->Ref_DonViMua;
            
                if(!isset($retval['Ban'][$code]))
                {
                    $retval['Ban'][$code]->DoiTuong             = 2; // Don vi khac
                    $retval['Ban'][$code]->MaCongTo             = '';
                    $retval['Ban'][$code]->TenCongTo            = '';
                    $retval['Ban'][$code]->LoaiGia              = 1; // Loai 1 gia
                    $retval['Ban'][$code]->DonViBan             = $item->DonViBan;
                    $retval['Ban'][$code]->RefDonViMuaChinh     = 'DONVIMUA'+$item->Ref_DonViMua;
                    $retval['Ban'][$code]->DonViMuaChinh        = $item->DonViMua;
                    $retval['Ban'][$code]->TongTienPhatCosPi    = 0;
                    $retval['Ban'][$code]->DonGiaChung          = $item->DonGia;
                    $retval['Ban'][$code]->DonGiaThapDiem       = 0;
                    $retval['Ban'][$code]->DonGiaTrungBinh      = 0;
                    $retval['Ban'][$code]->DonGiaCaoDiem        = 0;
                    $retval['Ban'][$code]->TongGiaChung         = $item->ThanhTien;
                    $retval['Ban'][$code]->TongGiaDienThapDiem  = 0;
                    $retval['Ban'][$code]->TongGiaDienTrungBinh = 0;
                    $retval['Ban'][$code]->TongGiaDienCaoDiem   = 0;
                    $retval['Ban'][$code]->TongSoDienChung      = $item->SanLuong;
                    $retval['Ban'][$code]->TongSoDienThapDiem   = 0;
                    $retval['Ban'][$code]->TongSoDienTrungBinh  = 0;
                    $retval['Ban'][$code]->TongSoDienCaoDiem    = 0;
                    $retval['Ban'][$code]->TongBaGia            = 0;
                    $retval['Ban'][$code]->TongBaSoDien         = 0;                    
                }
                else 
                {
                    $retval['Ban'][$code]->DonGiaChung         += $item->DonGia;
                    $retval['Ban'][$code]->TongGiaChung        += $item->ThanhTien;
                    $retval['Ban'][$code]->TongSoDienChung     += $item->SanLuong;
                }

            }
                    
            if($oldDongBan === '' || $sodongban > $oldDongBan)
            {
                $oldDongBan = $sodongban;
            }                      
            
            
            //$retval['SoDong']               = ($sodongban >= $sodongmua)?($sodongban + 1):($sodongmua + 1);
            $retval['TongDienMuaDienLuc']   += $electricLib->getTongDienQuangNinhConLaiChoSuDung();
            $retval['TongTienMuaDienLuc']   += $electricLib->getTongTienDienQuangNinhConLaiChoSuDung();
            $retval['CospiBanDienLuc']      += $electricLib->getCospiBanChoQuangNinh();
            $retval['TongDienBanDienLuc']   += $electricLib->getDienBanChoQuangNinh();
            $retval['TongTienBanDienLuc']   += $electricLib->getTienDienBanChoQuangNinh();
            $retval['TongDienMua']          += $electricLib->getTongDienMuaVao();
            $retval['TongTienMua']          += $electricLib->getTongTienDienMuaVao();
            $retval['TongDienBan']          += $electricLib->getTongDienBanRa();
            $retval['TongTienBan']          += $electricLib->getTongTienDienBanRa();
            $retval['ChenhLechMuaBan']      += $electricLib->getChenhLechMuaBan();
            $retval['TongTienBanCuoi']      += $electricLib->getTongTienBanTruChenhLech();
            $retval['DienConLaiSuDungCuoi'] += $electricLib->getDienConLaiSuDungCuoiCung();
            $retval['TienConLaiSuDungCuoi'] += $electricLib->getTienConLaiSuDungCuoiCung();            
        }
        
        if($oldDongMua === '')
        {
            $oldDongMua = $sodongmua;
        }
        
        if($oldDongBan === '')
        {
            $oldDongBan = $sodongban;
        }        
        
        $retval['SoDong']  = ($oldDongBan >= $oldDongMua)?($oldDongBan + 1):($oldDongMua + 1);
        
        // echo '<pre>'; print_r($retval); die;
        return $retval;
    
    }
    
    
}