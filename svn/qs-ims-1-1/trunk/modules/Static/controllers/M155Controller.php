<?php
/**
 * Class Static_M155Controller
 * Báo cáo theo dõi bán điện năng hàng tháng
 */
class Static_M155Controller extends Qss_Lib_Controller
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
        $electric  = new Qss_Model_Maintenance_Electric();
        $start     = $this->params->requests->getParam('start', 0);
        $end       = $this->params->requests->getParam('end', 0);
        $year      = $this->params->requests->getParam('year', 0);
    
        if($end == 0)
        {
            $end = $start;
        }
        
        $this->html->start  = @(int)$start;
        $this->html->end    = @(int)$end;
        $this->html->year   = @(int)$year;  
        $this->html->report = $this->getReportData(@(int)$start, @(int)$end, @(int)$year);  
    }

    public function getReportData($start, $end, $year)
    {
            // Xu ly du lieu in ra
        $excel_col  = array_flip(Qss_Lib_Const::$EXCEL_COLUMN);
        $report     = array();
        $electric   = new Qss_Model_Maintenance_Electric();
        $iBanRa      = -1;
        $banRa       = new stdClass();
        $tempBanRa   = new stdClass();
        $iBanDotXuat = -1;
        $banDotXuat  = new stdClass();
        $tempBanDotXuat = new stdClass();
        
        for($iMonth = $start; $iMonth <= $end; $iMonth++)
        {
            $oBanra      = $electric->getChiSoDienNangBanRa($iMonth, $year);
            $oBanDotXuat = $electric->getDienNangBanRaDotXuat($iMonth, $year);        

            
            foreach($oBanra as $dat)
            {
                $code = "{$dat->CongToIOID}-{$dat->Ky}";
                
                if(!isset($tempBanRa->{$code}))
                {
                    $tempBanRa->{$code} = $dat;
                }
                else 
                {
                    $tempBanRa->{$code}->HeSo                     = $dat->HeSo;
                    $tempBanRa->{$code}->ChiSoCuoi                = $dat->ChiSoCuoi;
                    $tempBanRa->{$code}->TongSo                  += $dat->TongSo;
                    $tempBanRa->{$code}->ChiSoCuoiThapDiem        = $dat->ChiSoCuoiThapDiem;
                    $tempBanRa->{$code}->TongSoThapDiem          += $dat->TongSoThapDiem;
                    $tempBanRa->{$code}->ChiSoCuoiTrungBinh       = $dat->ChiSoCuoiTrungBinh;
                    $tempBanRa->{$code}->TongSoTrungBinh         += $dat->TongSoTrungBinh;
                    $tempBanRa->{$code}->ChiSoCuoiCaoDiem         = $dat->ChiSoCuoiCaoDiem;
                    $tempBanRa->{$code}->TongSoCaoDiem           += $dat->TongSoCaoDiem;
                    $tempBanRa->{$code}->TongSoCoTonHao          += $dat->TongSoCoTonHao;
                    $tempBanRa->{$code}->TongSoCaoDiemCoTonHao   += $dat->TongSoCaoDiemCoTonHao;
                    $tempBanRa->{$code}->TongSoThapDiemCoTonHao  += $dat->TongSoThapDiemCoTonHao;
                    $tempBanRa->{$code}->TongSoTrungBinhCoTonHao += $dat->TongSoTrungBinhCoTonHao;
                    $tempBanRa->{$code}->TongTienPhatCosPi       += $dat->TongTienPhatCosPi;
                } 
            }
            
            foreach($oBanDotXuat as $dat)
            {
                $code = "{$dat->DonViMua}";
            
                if(!isset($tempBanRa->{$code}))
                {
                    $tempBanDotXuat->{$code} = $dat;
                }
                else
                {
                    $tempBanDotXuat->{$code}->SanLuong += $dat->SanLuong;
                }
            } 
        }
        
        foreach($tempBanRa as $tbr)
        {
            $banRa->{++$iBanRa} = $tbr;
        }
        
        foreach($tempBanDotXuat as $bdx)
        {
            $banDotXuat->{++$iBanDotXuat} = $bdx;
        }        
        
        
        // Triet xuat bao cao
        if (true) // Chi để ẩn code, ko có nghĩa gì khác
        {
            $rowIndex                   = 0;
            $colIndex                   = 0;
        
            // Cac bien luu lai gia tri cu de so sanh
            $olDoiTuongCungCap          = '';
            $oldLoaiGiaQuangNinh        = '';
            $oldViTri                   = '';
            $oldCongTo                  = '';
            $oldCongTo2                 = ''; // Dem so cong to theo vi tri
        
            // Cac gia tri index
            $tramChoDienLucQNIndex      = 0;
            $tramChoDienLucQNIndex2     = array();
            $donViNgoaiIndex            = 0;
            $quangNinh1GiaIndex         = 0;
        
            $demCongToTheoViTri         = array();
            $total                      = array();
            $memory                     = array();
        
        
            // Dem so cong to theo vi tri
            foreach($banRa as $reading)
            {
                // Dem so cong to theo vi tri
                if($oldCongTo2 != $reading->CongToIOID)
                {
                    if(isset($demCongToTheoViTri[$reading->ViTri]))
                    {
                        $demCongToTheoViTri[$reading->ViTri]++;
                    }
                    else
                    {
                        $demCongToTheoViTri[$reading->ViTri] = 1;
                    }
                }
                $oldCongTo2 = $reading->CongToIOID;
            }
        
            foreach($banRa as $reading)
            {
                // IN DOI TUONG CUNG CAP <DIEN LUC QUANG NINH/DON VI KHAC>
                if($olDoiTuongCungCap != $reading->DoiTuong)
                {
                    if($reading->DoiTuong == 1)
                    {
                        // Ghi nho lai vi tri can tinh toan cua dien luc quan ninh
                        $total['TongThapDiemChoDienLucQuangNinh']        = 0;
                        $total['TongTrungBinhChoDienLucQuangNinh']       = 0;
                        $total['TongCaoDiemChoDienLucQuangNinh']         = 0;
                        $total['TongTongCongSuatMuaChoDienLucQuangNinh'] = 0;
                        $memory['TongChoDienLucQuangNinh']               = $rowIndex;
        
                        // Tieu de cho khoi dien luc quang ninh
                        $report[$rowIndex][0]  = '<b>I</b>';
                        $report[$rowIndex][1]  = '<b>Điện lực Quảng Ninh</b>';//Điện lực Quảng Ninh
                        $report[$rowIndex][6]  = 0;  // Dien nang tieu thu trung binh
                        $report[$rowIndex][9]  = 0;  // Dien nang tieu thu cao diem
                        $report[$rowIndex][12] = 0;  // Dien nang tieu thu thap diem
                        $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                        $rowIndex++;
                    }
                    else
                    {
                        // Ghi nho lai vi tri can tinh toan cua cac don vi khac
                        $total['TongTongCongSuatMuaChoDonViKhac'] = 0;
                        $memory['TongChoDonViKhac']               = $rowIndex;
        
        
                        // Tieu de cho khoi don vi khac
                        $report[$rowIndex][0]  = '<b>II</b>';
                        $report[$rowIndex][1]  = '<b>Đơn vị ngoài (1 Giá)</b>';
                        $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                        $rowIndex++;
                    }
                }
        
                // IN TRUONG LOAI GIA <CHI AP DUNG CHO DIEN LUC QUANG NINH>
                if($reading->DoiTuong == 1)
                {
                    if($oldLoaiGiaQuangNinh != $reading->LoaiGia)
                    {
                        // In loai 1 gia dien luc quang ninh
                        if($reading->LoaiGia == 1)
                        {
                            // Luu lai vi tri dong tieu de mot gia dien luc quang ninh
                            $total['TongThapDiemChoDienLucQuangNinhMotGia']        = 0;
                            $total['TongTrungBinhChoDienLucQuangNinhMotGia']       = 0;
                            $total['TongCaoDiemChoDienLucQuangNinhMotGia']         = 0;
                            $total['TongTongCongSuatMuaChoDienLucQuangNinhMotGia'] = 0;
                            $memory['TongMotGiaDienLucQuangNinh']                  = $rowIndex;
        
                            // Tieu de cho khoi 1 gia dien luc quang ninh
                            $report[$rowIndex][0]  = '<b>B</b>';
                            $report[$rowIndex][1]  = '<b>Công tơ 1 giá</b>';
                            $report[$rowIndex][6]  = '';  // Dien nang tieu thu trung binh
                            $report[$rowIndex][9]  = '';  // Dien nang tieu thu cao diem
                            $report[$rowIndex][12] = '';  // Dien nang tieu thu thap diem
                            $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                            $rowIndex++;
                        }
                        else // In loai 3 gia dien luc quang ninh
                        {
                            // Luu lai vi tri dong tieu de ba gia dien luc quang ninh
                            $total['TongThapDiemChoDienLucQuangNinhBaGia']        = 0;
                            $total['TongTrungBinhChoDienLucQuangNinhBaGia']       = 0;
                            $total['TongCaoDiemChoDienLucQuangNinhBaGia']         = 0;
                            $total['TongTongCongSuatMuaChoDienLucQuangNinhBaGia'] = 0;
                            $memory['TongBaGiaDienLucQuangNinh']                  = $rowIndex;
        
                            // Tieu de cho khoi ba gia dien luc quang ninh
                            $report[$rowIndex][0]  = '<b>A</b>';
                            $report[$rowIndex][1]  = '<b>Công tơ 3 giá</b>';
                            $report[$rowIndex][6]  = 0;  // Dien nang tieu thu trung binh
                            $report[$rowIndex][9]  = 0;  // Dien nang tieu thu cao diem
                            $report[$rowIndex][12] = 0;  // Dien nang tieu thu thap diem
                            $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                            $rowIndex++;
                        }
                    }
                }
        
                // IN TRUONG VI TRI <DOI VOI DIEN LUC QUANG NINH 3 gia>
                if($reading->DoiTuong == 1 && $reading->LoaiGia == 2 && ($oldViTri != $reading->ViTri))
                {
                    $total['TongThapDiemChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]         = 0;
                    $total['TongTrungBinhChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]        = 0;
                    $total['TongCaoDiemChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]          = 0;
                    $total['TongTongCongSuatMuaChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]  = 0;
                    $memory['TongViTriCuaBaGiaDienLucQuangNinh'][$reading->ViTri]                   = $rowIndex;
        
        
                    $report[$rowIndex][0]  = ++$tramChoDienLucQNIndex;
                    $report[$rowIndex][1]  = $reading->ViTri;
                    $report[$rowIndex][6]  = 0;  // Dien nang tieu thu trung binh
                    $report[$rowIndex][9]  = 0;  // Dien nang tieu thu cao diem
                    $report[$rowIndex][12] = 0;  // Dien nang tieu thu thap diem
                    $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                    $rowIndex++;
        
                    $tramChoDienLucQNIndex2[$tramChoDienLucQNIndex]  = 0;
                }
        
                // IN TEN CONG TO <CHI AP DUNG CHO QUANG NINH BA GIA>
                if($reading->DoiTuong == 1
                    && ($demCongToTheoViTri[$reading->ViTri] > 1)
                    && $reading->LoaiGia == 2
                    && $oldCongTo != $reading->CongToIOID)
                {
                    $total['TongThapDiemChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]         = 0;
                    $total['TongTrungBinhChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]        = 0;
                    $total['TongCaoDiemChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]          = 0;
                    $total['TongTongCongSuatMuaChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]  = 0;
                    $memory['TongCongToCuaBaGiaDienLucQuangNinh'][$reading->CongToIOID]                   = $rowIndex;
        
        
                    $report[$rowIndex][0]  = strtolower($excel_col[++$tramChoDienLucQNIndex2[$tramChoDienLucQNIndex]]);
                    $report[$rowIndex][1]  = $reading->TenCongTo;
                    $report[$rowIndex][6]  = 0;  // Dien nang tieu thu trung binh
                    $report[$rowIndex][9]  = 0;  // Dien nang tieu thu cao diem
                    $report[$rowIndex][12] = 0;  // Dien nang tieu thu thap diem
                    $report[$rowIndex][15] = 0;  // Dien nang tieu thu tong cong suat mua
                    $rowIndex++;
                }
        
                // IN CHI SO THEO KY <CHI AP DUNG CHO QUANG NINH BA GIA>
                if($reading->DoiTuong == 1 && $reading->LoaiGia == 2)
                {
                    $tongSoTrungBinh = $reading->TongSoTrungBinh  * $reading->HeSoTonHao;
                    $tongSoCaoDiem   = $reading->TongSoCaoDiem * $reading->HeSoTonHao;
                    $tongSoThapDiem  = $reading->TongSoThapDiem  * $reading->HeSoTonHao;
                    $tongSoCSMua     = $reading->TongSo * $reading->HeSoTonHao;
        
        
                    $total['TongThapDiemChoDienLucQuangNinh']        += $tongSoThapDiem;
                    $total['TongTrungBinhChoDienLucQuangNinh']       += $tongSoTrungBinh;
                    $total['TongCaoDiemChoDienLucQuangNinh']         += $tongSoCaoDiem;
                    $total['TongTongCongSuatMuaChoDienLucQuangNinh'] += $tongSoCSMua;
        
                    $total['TongThapDiemChoDienLucQuangNinhBaGia']        += $tongSoThapDiem;
                    $total['TongTrungBinhChoDienLucQuangNinhBaGia']       += $tongSoTrungBinh;
                    $total['TongCaoDiemChoDienLucQuangNinhBaGia']         += $tongSoCaoDiem;
                    $total['TongTongCongSuatMuaChoDienLucQuangNinhBaGia'] += $tongSoCSMua;
        
                    if(isset($memory['TongViTriCuaBaGiaDienLucQuangNinh'][$reading->ViTri]))
                    {
                        $total['TongThapDiemChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]         += $tongSoThapDiem;
                        $total['TongTrungBinhChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]        += $tongSoTrungBinh;
                        $total['TongCaoDiemChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]          += $tongSoCaoDiem;
                        $total['TongTongCongSuatMuaChoViTriCuaDienLucQuangNinhBaGia'][$reading->ViTri]  += $tongSoCSMua;
                    }
        
                    if(isset($memory['TongCongToCuaBaGiaDienLucQuangNinh'][$reading->CongToIOID]))
                    {
                        $total['TongThapDiemChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]         += $tongSoThapDiem;
                        $total['TongTrungBinhChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]        += $tongSoTrungBinh;
                        $total['TongCaoDiemChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]          += $tongSoCaoDiem;
                        $total['TongTongCongSuatMuaChoCongToCuaDienLucQuangNinhBaGia'][$reading->CongToIOID]  += $tongSoCSMua;
                    }
        
        
                    $colIndex = 0;
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = "Kỳ ".$reading->Ky;
                    $report[$rowIndex][$colIndex++]  = $reading->HeSoTonHao;
                    $report[$rowIndex][$colIndex++]  = $reading->HeSoCongTo;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoDauTrungBinh;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoCuoiTrungBinh;
                    $report[$rowIndex][$colIndex++]  = $tongSoTrungBinh;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoDauCaoDiem;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoCuoiCaoDiem;
                    $report[$rowIndex][$colIndex++]  = $tongSoCaoDiem;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoDauThapDiem;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoCuoiThapDiem;
                    $report[$rowIndex][$colIndex++]  = $tongSoThapDiem;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoDau;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoCuoi;
                    $report[$rowIndex][$colIndex++]  = $tongSoCSMua;
                    $rowIndex++;
                }
        
                // IN TRUONG VOI LOAI MOT GIA
                if($reading->LoaiGia == 1)
                {
                    $tongSoCSMua     = $reading->TongSo * $reading->HeSoTonHao;
        
                    if($reading->DoiTuong == 1)
                    {
                        $total['TongTongCongSuatMuaChoDienLucQuangNinh'] += $tongSoCSMua;
                        $total['TongTongCongSuatMuaChoDienLucQuangNinhMotGia'] += $tongSoCSMua;
                    }
                    else
                    {
                        $total['TongTongCongSuatMuaChoDonViKhac'] += $tongSoCSMua;
                    }
        
                    $colIndex = 0;
                    $report[$rowIndex][$colIndex++]  = ($reading->DoiTuong != 1)?++$donViNgoaiIndex:++$quangNinh1GiaIndex;
                    $report[$rowIndex][$colIndex++]  = $reading->ViTri;
                    $report[$rowIndex][$colIndex++]  = $reading->HeSoTonHao;
                    $report[$rowIndex][$colIndex++]  = $reading->HeSoCongTo;
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = '';
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoDau;
                    $report[$rowIndex][$colIndex++]  = $reading->ChiSoCuoi;
                    $report[$rowIndex][$colIndex++]  = $tongSoCSMua;
                    $rowIndex++;
                }
        
                $olDoiTuongCungCap   = $reading->DoiTuong;
                $oldLoaiGiaQuangNinh = $reading->LoaiGia;
                $oldViTri            = $reading->ViTri;
                $oldCongTo           = $reading->CongToIOID;
            }
            
            
            foreach($banDotXuat as $reading)
            {
                // IN TRUONG VOI LOAI MOT GIA
                $tongSoCSMua     = $reading->SanLuong;
            
                $total['TongTongCongSuatMuaChoDonViKhac'] += $tongSoCSMua;
            
                $colIndex = 0;
                $report[$rowIndex][$colIndex++]  = ++$donViNgoaiIndex;
                $report[$rowIndex][$colIndex++]  = $reading->DonViMua;
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = '';
                $report[$rowIndex][$colIndex++]  = $tongSoCSMua;
                $rowIndex++;
            }
        
            // SET LAI GIA TRI TONG
            //$memory['TongCongToCuaBaGiaDienLucQuangNinh'][$reading->CongToIOID]                   = $rowIndex;
            //$memory['TongViTriCuaBaGiaDienLucQuangNinh'][$reading->ViTri]                   = $rowIndex;
            //$memory['TongBaGiaDienLucQuangNinh']                  = $rowIndex;
            //$memory['TongMotGiaDienLucQuangNinh']                  = $rowIndex;
            //$memory['TongChoDonViKhac']               = $rowIndex;
            //$memory['TongChoDienLucQuangNinh']               = $rowIndex;
        
            // DIEN LUC QUANG NINH
            if(isset($memory['TongChoDienLucQuangNinh']) && isset($report[$memory['TongChoDienLucQuangNinh']]))
            {
                $rowIndex = $memory['TongChoDienLucQuangNinh'];
        
                $report[$rowIndex][6]  = $total['TongTrungBinhChoDienLucQuangNinh'] ;  // Dien nang tieu thu trung binh
                $report[$rowIndex][9]  = $total['TongCaoDiemChoDienLucQuangNinh'];  // Dien nang tieu thu cao diem
                $report[$rowIndex][12] = $total['TongThapDiemChoDienLucQuangNinh']  ;  // Dien nang tieu thu thap diem
                $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoDienLucQuangNinh'];  // Dien nang tieu thu tong cong suat mua
            }
        
            // BA GIA DIEN LUC QUANG NINH
            if(isset($memory['TongBaGiaDienLucQuangNinh']) && isset($report[$memory['TongBaGiaDienLucQuangNinh']]))
            {
                $rowIndex = $memory['TongBaGiaDienLucQuangNinh'];
        
                $report[$rowIndex][6]  = $total['TongTrungBinhChoDienLucQuangNinhBaGia'] ;  // Dien nang tieu thu trung binh
                $report[$rowIndex][9]  = $total['TongCaoDiemChoDienLucQuangNinhBaGia'];  // Dien nang tieu thu cao diem
                $report[$rowIndex][12] = $total['TongThapDiemChoDienLucQuangNinhBaGia']  ;  // Dien nang tieu thu thap diem
                $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoDienLucQuangNinhBaGia'];  // Dien nang tieu thu tong cong suat mua
            }
        
            // VI TRI  BA GIA DIEN LUC QUANG NINH
            if(isset($memory['TongViTriCuaBaGiaDienLucQuangNinh']))
            {
                foreach($memory['TongViTriCuaBaGiaDienLucQuangNinh'] as $refViTri=>$rowIndex)
                {
                    $report[$rowIndex][6]  = $total['TongTrungBinhChoViTriCuaDienLucQuangNinhBaGia'][$refViTri];  // Dien nang tieu thu trung binh
                    $report[$rowIndex][9]  = $total['TongCaoDiemChoViTriCuaDienLucQuangNinhBaGia'][$refViTri] ;  // Dien nang tieu thu cao diem
                    $report[$rowIndex][12] = $total['TongThapDiemChoViTriCuaDienLucQuangNinhBaGia'][$refViTri] ;  // Dien nang tieu thu thap diem
                    $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoViTriCuaDienLucQuangNinhBaGia'][$refViTri];  // Dien nang tieu thu tong cong suat mua
                }
            }
        
            // CONG TO CHO BA GIA QUANG NINH
            if(isset($memory['TongCongToCuaBaGiaDienLucQuangNinh']))
            {
                foreach($memory['TongCongToCuaBaGiaDienLucQuangNinh'] as $refCongTo=>$rowIndex)
                {
                    $report[$rowIndex][6]  = $total['TongTrungBinhChoCongToCuaDienLucQuangNinhBaGia'][$refCongTo];  // Dien nang tieu thu trung binh
                    $report[$rowIndex][9]  = $total['TongCaoDiemChoCongToCuaDienLucQuangNinhBaGia'][$refCongTo] ;  // Dien nang tieu thu cao diem
                    $report[$rowIndex][12] = $total['TongThapDiemChoCongToCuaDienLucQuangNinhBaGia'][$refCongTo] ;  // Dien nang tieu thu thap diem
                    $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoCongToCuaDienLucQuangNinhBaGia'][$refCongTo];  // Dien nang tieu thu tong cong suat mua
                }
            }
        
            // Tong mot gia cho dien luc quang ninh
            if(isset($memory['TongMotGiaDienLucQuangNinh']) && isset($report[$memory['TongMotGiaDienLucQuangNinh']]))
            {
                $rowIndex = $memory['TongMotGiaDienLucQuangNinh'];
        
                $report[$rowIndex][6]  = $total['TongTrungBinhChoDienLucQuangNinhMotGia'] ;  // Dien nang tieu thu trung binh
                $report[$rowIndex][9]  = $total['TongCaoDiemChoDienLucQuangNinhMotGia'];  // Dien nang tieu thu cao diem
                $report[$rowIndex][12] = $total['TongThapDiemChoDienLucQuangNinhMotGia']  ;  // Dien nang tieu thu thap diem
                $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoDienLucQuangNinhMotGia'];  // Dien nang tieu thu tong cong suat mua
            }
        
            // Tong mot gia cho don vi ngoai
            if(isset($memory['TongChoDonViKhac']) && isset($report[$memory['TongChoDonViKhac']]))
            {
                $rowIndex = $memory['TongChoDonViKhac'];
        
                $report[$rowIndex][15] = $total['TongTongCongSuatMuaChoDonViKhac'];  // Dien nang tieu thu tong cong suat mua
            }
        }
        
        return $report;
    }
}